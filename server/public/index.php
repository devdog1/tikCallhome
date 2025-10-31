<?php
// Include the configuration file
require_once('../config.php');

// Get data from the request
$serialNumber = $_GET['serial'] ?? null;
$model = $_GET['model'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'];

if (!$serialNumber || !$model) {
    http_response_code(400);
    echo "Missing 'serial' or 'model' parameter.";
    exit;
}

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the router exists
    $stmt = $pdo->prepare("SELECT id FROM routers WHERE serial_number = :serial_number");
    $stmt->execute(['serial_number' => $serialNumber]);
    $router = $stmt->fetch();

    if ($router) {
        // Update existing router
        $stmt = $pdo->prepare("UPDATE routers SET ip_address = :ip_address, last_seen = NOW() WHERE id = :id");
        $stmt->execute([
            'ip_address' => $ipAddress,
            'id' => $router['id']
        ]);
    } else {
        // Insert new router with adopted status as false
        $stmt = $pdo->prepare("INSERT INTO routers (serial_number, model, ip_address, last_seen, adopted) VALUES (:serial_number, :model, :ip_address, NOW(), false)");
        $stmt->execute([
            'serial_number' => $serialNumber,
            'model' => $model,
            'ip_address' => $ipAddress
        ]);
    }

    echo "Router information updated successfully.";

} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
