<?php
// Include the configuration file
require_once('../../database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $command = $_POST['command'];
    $check_command = $_POST['check_command'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $target = ($type === 'model' || $type === 'serial' || $type === 'group') ? $_POST['target'] : null;

    try {

        $stmt = $pdo->prepare("INSERT INTO commands (command, check_command, description, type, target) VALUES (:command, :check_command, :description, :type, :target)");
        $stmt->execute([
            'command' => $command,
            'check_command' => $check_command,
            'description' => $description,
            'type' => $type,
            'target' => $target
        ]);

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
