<?php
require_once('../database.php');

if (isset($_GET['serial']) && isset($_GET['api_key'])) {
    $serialNumber = $_GET['serial'];
    $apiKey = $_GET['api_key'];

    try {
        // Find the router by serial number, it must be adopted and in pull mode.
        $stmt = $pdo->prepare("SELECT * FROM routers WHERE serial_number = :serial AND adopted = true AND execution_method = 'pull'");
        $stmt->execute(['serial' => $serialNumber]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router && $router['api_key'] === $apiKey) {
            // Get all applicable, unexecuted commands
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

            // Start generating the script
            $scriptContent = "# Mikrotik Command Script generated on " . date('Y-m-d H:i:s') . "\n";

            foreach ($commands as $command) {
                if (!empty($command['check_command'])) {
                    $scriptContent .= ":if ([:len [" . $command['check_command'] . "]] = 0) do={\n";
                    $scriptContent .= "    " . $command['command'] . "\n";
                    $scriptContent .= "}\n";
                } else {
                    $scriptContent .= $command['command'] . "\n";
                }
                // Log the command as 'delivered'
                $stmt = $pdo->prepare("INSERT INTO router_commands (router_id, command_id, executed_at, status) VALUES (:router_id, :command_id, NOW(), 'delivered')");
                $stmt->execute(['router_id' => $router['id'], 'command_id' => $command['id']]);
            }

            // Serve the script as a file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=\"commands.rsc\"');
            echo $scriptContent;

        } else {
            http_response_code(401);
            echo "# Unauthorized: Invalid serial number or API key.";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "# Database error.";
    }
} else {
    http_response_code(400);
    echo "# Serial number and API key are required.";
}
