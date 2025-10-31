<?php
// Include the configuration file
require_once('../../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $routerId = $_POST['router_id'];

    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate a unique API key
        $apiKey = bin2hex(random_bytes(16));

        // Mark the router as adopted and save the API key
        $stmt = $pdo->prepare("UPDATE routers SET adopted = true, api_key = :api_key WHERE id = :id");
        $stmt->execute(['api_key' => $apiKey, 'id' => $routerId]);

        // Get the router's full details
        $stmt = $pdo->prepare("SELECT * FROM routers WHERE id = :id");
        $stmt->execute(['id' => $routerId]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router) {
            // Check if the router's group has a base template
            if ($router['group_id']) {
                $stmt = $pdo->prepare("SELECT t.content, t.name FROM groups g JOIN config_templates t ON g.base_template_id = t.id WHERE g.id = :group_id");
                $stmt->execute(['group_id' => $router['group_id']]);
                $template = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($template) {
                    // Create a command from the template
                    $stmt = $pdo->prepare("INSERT INTO commands (command, description, type, target) VALUES (:command, :desc, 'serial', :target)");
                    $stmt->execute(['command' => $template['content'], 'desc' => "Base Template: {$template['name']}", 'target' => $router['serial_number']]);
                }
            }

            // If the router is in push mode, SSH in and inject the API key
            if ($router['execution_method'] == 'push') {
                $connection = ssh2_connect($router['ip_address'], 22);
                if ($connection) {
                    foreach ($sshCredentials as $cred) {
                        if (ssh2_auth_password($connection, $cred['user'], $cred['pass'])) {
                            $command = "
                                :global content [/file get [find name=call_home.rsc] contents];
                                :global apiKeyLine [:find \$content \":local apiKey\"];
                                :global part1 [:pick \$content 0 \$apiKeyLine];
                                :global part2 [:pick \$content ([:find \$content \"\\n\" \$apiKeyLine]) ([:len \$content])];
                                /file set [find name=call_home.rsc] contents=(\$part1 . \":local apiKey \\\"$apiKey\\\"\" . \$part2);
                            ";
                            ssh2_exec($connection, $command);
                            break;
                        }
                    }
                }
            }

            // Create the initial password command
            $command = "/user set [find name=admin] password=\"$defaultNewPassword\"";
            $check_command = "/user get [find name=admin] password"; // Simple check

            $stmt = $pdo->prepare("
                INSERT INTO commands (command, check_command, description, type, target)
                VALUES (:command, :check_command, :description, 'serial', :target)
            ");
            $stmt->execute([
                'command' => $command,
                'check_command' => $check_command,
                'description' => 'Set initial admin password',
                'target' => $router['serial_number']
            ]);

            // Trigger the SSH executor for this router in the background
            $executorPath = realpath(__DIR__ . '/../../ssh_executor.php');
            shell_exec("php $executorPath $routerId > /dev/null 2>&1 &");
        }

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
