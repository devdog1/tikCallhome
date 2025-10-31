<?php
// Include the configuration file
require_once('../../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $routerId = $_POST['router_id'];

    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Mark the router as adopted
        $stmt = $pdo->prepare("UPDATE routers SET adopted = true WHERE id = :id");
        $stmt->execute(['id' => $routerId]);

        // Get the router's serial number
        $stmt = $pdo->prepare("SELECT serial_number FROM routers WHERE id = :id");
        $stmt->execute(['id' => $routerId]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($router) {
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
