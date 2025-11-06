<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once '../../user.php';

$user_handler = new User($pdo);

require_once 'check_permission.php';

check_permission('manage_commands');

if (isset($_GET['id'])) {
    $commandId = $_GET['id'];
    try {
        $pdo->beginTransaction();

        // First, delete any associations in router_commands
        $stmt = $pdo->prepare("DELETE FROM router_commands WHERE command_id = :id");
        $stmt->execute(['id' => $commandId]);

        // Then, delete the command itself
        $stmt = $pdo->prepare("DELETE FROM commands WHERE id = :id");
        $stmt->execute(['id' => $commandId]);

        log_user_action($pdo, $_SESSION['user_id'], null, "Command #{$commandId} deleted");

        $pdo->commit();

        $_SESSION['success_message'] = "Command deleted successfully.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("Location: commands.php");
    exit;
}
