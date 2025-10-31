<?php
require_once('../../config.php');

header('Content-Type: application/json');

if (isset($_GET['router_id'])) {
    $routerId = $_GET['router_id'];
    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get the router's details
        $stmt = $pdo->prepare("SELECT * FROM routers WHERE id = :id");
        $stmt->execute(['id' => $routerId]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router) {
            // Get all applicable commands
            $stmt = $pdo->prepare("
                SELECT c.description, c.command FROM commands c
                WHERE
                    c.type = 'generic' OR
                    (c.type = 'group' AND c.target = :group_id) OR
                    (c.type = 'model' AND c.target = :model) OR
                    (c.type = 'serial' AND c.target = :serial_number)
            ");
            $stmt->execute([
                'group_id' => $router['group_id'],
                'model' => $router['model'],
                'serial_number' => $router['serial_number']
            ]);
            $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($commands);
        } else {
            echo json_encode(['error' => 'Router not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode(['error' => 'No router ID provided']);
}
