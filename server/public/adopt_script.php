<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../database.php');

// This script handles the initial call-home from a pull-mode router.
// It captures the router's IP address and handles registration and adoption.

if (isset($_GET['serial']) && isset($_GET['psk']) && isset($_GET['model'])) {
    $serialNumber = $_GET['serial'];
    $model = $_GET['model'];
    $psk = $_GET['psk'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // 1. Validate the pre-shared key
    if ($psk !== $adoptionPsk) {
        http_response_code(401);
        echo "# Unauthorized: Invalid pre-shared key.";
        exit;
    }

    try {
        // Check if the router already exists
        $stmt = $pdo->prepare("SELECT * FROM routers WHERE serial_number = :serial");
        $stmt->execute(['serial' => $serialNumber]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$router) {
            // 2. Router is unknown, add it as pending with its IP and current time
            $insertStmt = $pdo->prepare(
                "INSERT INTO routers (serial_number, model, ip_address, last_seen, adopted, execution_method, initial_pull_complete) VALUES (:serial, :model, :ip, NOW(), false, 'pull', false)"
            );
            $insertStmt->execute(['serial' => $serialNumber, 'model' => $model, 'ip' => $ipAddress]);

            header('Content-Type: text/plain');
            echo "# New router registered. Waiting for adoption approval.";

        } else {
            // Router is known, update its IP and last_seen timestamp
            $updateIpStmt = $pdo->prepare("UPDATE routers SET ip_address = :ip, last_seen = NOW() WHERE id = :id");
            $updateIpStmt->execute(['ip' => $ipAddress, 'id' => $router['id']]);

            if ($router['adopted'] == false) {
                // 3. Router is still pending adoption
                header('Content-Type: text/plain');
                echo "# Router is pending adoption. Waiting for approval.";

            } else if ($router['initial_pull_complete'] == false) {
                // 4. Router is adopted and ready for API key provisioning
                $newScriptContent = <<<MIKROTIK
# Mikrotik Call-Home Script for PULL Method (API Key Provisioned)
:local serverUrl "{$pullScriptUrl}"
:local apiKey "{$router['api_key']}"
:local serialNumber [/system routerboard get serial-number]
:local scriptUrl "\\\$serverUrl?serial=\\\$serialNumber&api_key=\\\$apiKey"
:local scriptName "commands.rsc"
/tool fetch url=\\\$scriptUrl dst-path=\\\$scriptName mode=https
:if ([:len [/file find name=\\\$scriptName]] > 0) do={
    /log info "Downloaded new command script, importing..."
    /import \\\$scriptName
    /file remove \\\$scriptName
    /log info "Script import complete."
} else { /log info "No new command script downloaded." }
MIKROTIK;

                $onetimeScript = ":log info \"Adoption complete. Provisioning API key.\";\r\n";
                $onetimeScript .= "/file set [find name=\"call_home_pull.rsc\"] contents='" . str_replace("'", "\\'", $newScriptContent) . "';\r\n";

                $updateStmt = $pdo->prepare("UPDATE routers SET initial_pull_complete = true WHERE id = :id");
                $updateStmt->execute(['id' => $router['id']]);

                header('Content-Type: text/plain');
                echo $onetimeScript;

            } else {
                // 5. Router is adopted and provisioned, just checking in
                header('Content-Type: text/plain');
                echo "# Router check-in successful.";
            }
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo "# Database error.";
    }
} else {
    http_response_code(400);
    echo "# Serial number, model, and pre-shared key are required.";
}
