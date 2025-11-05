<?php
// Include the configuration file
require_once('../../database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $routerId = $_POST['router_id'];
    $routerName = $_POST['router_name'];

    try {

        // Generate a unique API key and a secure password for the managed admin user
        $apiKey = bin2hex(random_bytes(16));
        $managedPassword = bin2hex(random_bytes(12)); // 24 characters long

        // Mark the router as adopted, save the API key, set the name, and store the password
        $stmt = $pdo->prepare("UPDATE routers SET adopted = true, api_key = :api_key, name = :name, local_admin_password = :password WHERE id = :id");
        $stmt->execute([
            'api_key' => $apiKey,
            'name' => $routerName,
            'password' => $managedPassword,
            'id' => $routerId
        ]);

        // Get the router's serial number for command targeting
        $stmt_get_serial = $pdo->prepare("SELECT serial_number FROM routers WHERE id = :id");
        $stmt_get_serial->execute(['id' => $routerId]);
        $serial = $stmt_get_serial->fetchColumn();

        // Create the command to set the system identity
        $identityCommand = "/system identity set name=\"$routerName\"";
        $stmt = $pdo->prepare("INSERT INTO commands (command, description, type, target) VALUES (:command, :desc, 'serial', :target)");
        $stmt->execute(['command' => $identityCommand, 'desc' => "Set System Name", 'target' => $serial]);

        // Create the command to add the managed admin user
        $adminUserCommand = "/user add name=managed-admin group=full password=\"$managedPassword\"";
        $stmt = $pdo->prepare("INSERT INTO commands (command, description, type, target) VALUES (:command, :desc, 'serial', :target)");
        $stmt->execute(['command' => $adminUserCommand, 'desc' => "Create Managed Admin User", 'target' => $serial]);

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
