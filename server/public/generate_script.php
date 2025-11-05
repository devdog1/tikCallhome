<?php
require_once('../database.php');

if (isset($_GET['serial'])) {
    $serialNumber = $_GET['serial'];
    $apiKey = $_GET['api_key'] ?? null;

    try {
        // Find the router by serial number, it must be adopted and in pull mode.
        $stmt = $pdo->prepare("SELECT * FROM routers WHERE serial_number = :serial AND adopted = true AND execution_method = 'pull'");
        $stmt->execute(['serial' => $serialNumber]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router) {
            // Check if this is the first pull after adoption.
            if ($router['initial_pull_complete'] == false) {
                // This is the first pull. Generate a one-time script to set the API key.
                $scriptContent = ":log info \"First pull after adoption. Configuring API key.\";\n\n";
                $scriptContent .= "/file set [find name=\"call_home_pull.rsc\"] contents=\"";
                $scriptContent .= "/tool fetch url=\\\"http://" . $serverIp . "/server/public/generate_script.php?serial=$serialNumber&api_key=" . $router['api_key'] . "\\\" dst-path=latest_commands.rsc; ";
                $scriptContent .= "/import file-name=latest_commands.rsc;\";\n\n";

                // Mark the initial pull as complete.
                $updateStmt = $pdo->prepare("UPDATE routers SET initial_pull_complete = true WHERE id = :id");
                $updateStmt->execute(['id' => $router['id']]);

            } else {
                // This is a subsequent pull. The API key must be valid.
                if (empty($apiKey) || $router['api_key'] !== $apiKey) {
                    http_response_code(401);
                    echo "# Unauthorized: Invalid or missing API key.";
                    exit;
                }

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
            header('Content-Disposition: attachment; filename="commands.rsc"');
            echo $scriptContent;

        } else {
            http_response_code(404);
            echo "# Router not found or not configured for pull method.";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "# Database error.";
    }
} else {
    http_response_code(400);
    echo "# No serial number provided.";
}
