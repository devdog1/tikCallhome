<?php
// Include the configuration file
require_once(__DIR__ . '/config.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if a specific router ID was passed as a command-line argument
    $routerId = $argv[1] ?? null;

    $query = "SELECT * FROM routers WHERE adopted = true";
    if ($routerId) {
        $query .= " AND id = :router_id";
    }
    $stmt = $pdo->prepare($query);
    if ($routerId) {
        $stmt->execute(['router_id' => $routerId]);
    } else {
        $stmt->execute();
    }
    $routers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($routers as $router) {
        echo "Processing router: {$router['serial_number']}\n";

        // Get commands for this router
        $stmt = $pdo->prepare("
            SELECT c.* FROM commands c
            LEFT JOIN router_commands rc ON c.id = rc.command_id AND rc.router_id = :router_id
            WHERE rc.id IS NULL AND (
                c.type = 'generic' OR
                (c.type = 'group' AND c.target = :group_id) OR
                (c.type = 'model' AND c.target = :model) OR
                (c.type = 'serial' AND c.target = :serial_number)
            )
        ");
        $stmt->execute([
            'router_id' => $router['id'],
            'group_id' => $router['group_id'],
            'model' => $router['model'],
            'serial_number' => $router['serial_number']
        ]);
        $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($commands)) {
            echo "No new commands for this router.\n";
            continue;
        }

        // Connect via SSH
        $connection = ssh2_connect($router['ip_address'], 22);
        if (!$connection) {
            echo "Could not initiate SSH connection to router: {$router['serial_number']}\n";
            continue;
        }

        $authenticated = false;
        foreach ($sshCredentials as $cred) {
            if (ssh2_auth_password($connection, $cred['user'], $cred['pass'])) {
                $authenticated = true;
                echo "Successfully authenticated to router {$router['serial_number']} with user {$cred['user']}\n";
                break;
            }
        }

        if (!$authenticated) {
            echo "SSH authentication failed for router: {$router['serial_number']}\n";
            continue;
        }

        foreach ($commands as $command) {
            echo "Processing command: {$command['command']}\n";

            // If a check_command is defined, run it to see if the command needs to be executed.
            if (!empty($command['check_command'])) {
                $stream = ssh2_exec($connection, $command['check_command']);
                stream_set_blocking($stream, true);
                $output = trim(stream_get_contents($stream));

                if (!empty($output)) {
                    echo "Skipping command, check returned: $output\n";
                    // Optionally, log that this command was skipped
                    continue; // Skip to the next command
                }
            }

            // Execute the main command
            echo "Executing command: {$command['command']}\n";
            $stream = ssh2_exec($connection, $command['command']);
            stream_set_blocking($stream, true);
            $output = stream_get_contents($stream);

            // Log the command execution
            $stmt = $pdo->prepare("INSERT INTO router_commands (router_id, command_id, executed_at) VALUES (:router_id, :command_id, NOW())");
            $stmt->execute([
                'router_id' => $router['id'],
                'command_id' => $command['id']
            ]);
        }
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
