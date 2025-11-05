<?php
session_start();
require_once('../../database.php');
require_once('../../user.php');
require_once('check_permission.php');

$user_handler = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_permission('manage_commands');

    $command = $_POST['command'];
    $check_command = $_POST['check_command'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $target = null;

    // Determine the target based on the selected type
    if ($type === 'group') {
        $target = $_POST['target_group'];
    } elseif ($type === 'model') {
        $target = $_POST['target_model'];
    } elseif ($type === 'serial') {
        $target = $_POST['target_serial'];
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO commands (command, check_command, description, type, target)
             VALUES (:command, :check_command, :description, :type, :target)"
        );
        $stmt->execute([
            'command' => $command,
            'check_command' => $check_command,
            'description' => $description,
            'type' => $type,
            'target' => $target
        ]);

        // Redirect back to the commands page
        header("Location: commands.php");
        exit;
    } catch (PDOException $e) {
        // You might want to handle this more gracefully
        die("Database error: " . $e->getMessage());
    }
} else {
    // If accessed directly, redirect to the main page or show an error
    header("Location: commands.php");
    exit;
}
