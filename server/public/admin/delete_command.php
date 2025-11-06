<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once 'check_permission.php';

check_permission('manage_commands');

if (isset($_GET['id'])) {
    $commandId = $_GET['id'];
    try {
        log_user_action($pdo, $_SESSION['user_id'], null, "Command #{$commandId} deleted");
        // First, delete any associations in router_commands
        $stmt = $pdo->prepare("DELETE FROM router_commands WHERE command_id = :id");
        $stmt->execute(['id' => $_GET['id']]);

        // Then, delete the command itself
        $stmt = $pdo->prepare("DELETE FROM commands WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
