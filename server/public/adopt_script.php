<?php
require_once('../database.php');

if (isset($_GET['serial'])) {
    $serialNumber = $_GET['serial'];

    try {
        // Find the router by serial number, it must be adopted and in pull mode.
        $stmt = $pdo->prepare("SELECT * FROM routers WHERE serial_number = :serial AND adopted = true AND execution_method = 'pull'");
        $stmt->execute(['serial' => $serialNumber]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router && $router['initial_pull_complete'] == false) {
            // This is the first pull. Generate a one-time script to set the API key.
            $scriptContent = ":log info \"First pull after adoption. Configuring API key.\";\n\n";
            $scriptContent .= "/file set [find name=\"call_home_pull.rsc\"] contents=\"";
            $scriptContent .= "/tool fetch url=\\\"" . $pullScriptUrl . "?serial=$serialNumber&api_key=" . $router['api_key'] . "\\\" dst-path=latest_commands.rsc; ";
            $scriptContent .= "/import file-name=latest_commands.rsc;\";\n\n";

            // Mark the initial pull as complete.
            $updateStmt = $pdo->prepare("UPDATE routers SET initial_pull_complete = true WHERE id = :id");
            $updateStmt->execute(['id' => $router['id']]);

            // Serve the script as a file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="adopt.rsc"');
            echo $scriptContent;

        } else {
            http_response_code(404);
            echo "# Router not found, not in pull mode, or initial pull already complete.";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "# Database error.";
    }
} else {
    http_response_code(400);
    echo "# No serial number provided.";
}
