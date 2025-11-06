<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../database.php');

// This script is called by a router for the initial, keyless adoption pull.
// It responds with a one-time script that replaces the router's existing
// 'call_home_pull.rsc' with a new version containing the API key.

if (isset($_GET['serial']) && isset($_GET['psk'])) {
    $serialNumber = $_GET['serial'];
    $psk = $_GET['psk'];

    // Validate the pre-shared key
    if ($psk !== $adoptionPsk) {
        http_response_code(401);
        echo "# Unauthorized: Invalid pre-shared key.";
        exit;
    }

    try {
        // Find the router by serial number. It must be adopted, in pull mode,
        // and not have completed its initial pull.
        $stmt = $pdo->prepare("SELECT id, api_key FROM routers WHERE serial_number = :serial AND adopted = true AND execution_method = 'pull' AND initial_pull_complete = false");
        $stmt->execute(['serial' => $serialNumber]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router) {
            // The router is valid and ready for the second stage of adoption.

            // 1. Generate the new content for the 'call_home_pull.rsc' file.
            $newScriptContent = <<<MIKROTIK
# Mikrotik Call-Home Script for PULL Method (API Key Provisioned)

:local serverUrl "{$pullScriptUrl}"
:local apiKey "{$router['api_key']}"
:local serialNumber [/system routerboard get serial-number]

# Fetch and Run Command Script
:local scriptUrl "\\\$serverUrl?serial=\\\$serialNumber&api_key=\\\$apiKey"
:local scriptName "commands.rsc"

# Fetch the script
/tool fetch url=\\\$scriptUrl dst-path=\\\$scriptName mode=https

# If the script was downloaded, import it
:if ([:len [/file find name=\\\$scriptName]] > 0) do={
    /log info "Downloaded new command script, importing..."
    /import \\\$scriptName
    /file remove \\\$scriptName
    /log info "Script import complete."
} else {
    /log info "No new command script downloaded."
}
MIKROTIK;

            // 2. Create the one-time script to be executed by the router.
            $onetimeScript = ":log info \"Adoption complete. Provisioning API key and pull script.\";\r\n";
            $onetimeScript .= "/file set [find name=\"call_home_pull.rsc\"] contents='" . str_replace("'", "\\'", $newScriptContent) . "';\r\n";

            // 3. Mark the initial pull as complete in the database.
            $updateStmt = $pdo->prepare("UPDATE routers SET initial_pull_complete = true WHERE id = :id");
            $updateStmt->execute(['id' => $router['id']]);

            // 4. Serve the one-time script to the router.
            header('Content-Type: text/plain');
            echo $onetimeScript;

        } else {
            // Router not found, not in the correct state, or has already been provisioned.
            http_response_code(404);
            echo "# Router not found, not in pull mode, or initial pull already complete.";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "# Database error.";
    }
} else {
    http_response_code(400);
    echo "# Serial number and pre-shared key are required.";
}
