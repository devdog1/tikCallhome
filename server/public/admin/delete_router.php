<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once 'check_permission.php';

check_permission('manage_routers');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $pdo->beginTransaction();

        // First, delete related commands to maintain referential integrity
        $stmt = $pdo->prepare("DELETE FROM router_commands WHERE router_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM command_logs WHERE router_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM routers WHERE id = ?");
        $stmt->execute([$id]);

        log_user_action($pdo, $_SESSION['user_id'], $id, "Router deleted");

        $pdo->commit();

        $_SESSION['success_message'] = "Router deleted successfully.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("Location: routers.php");
    exit;
} else {
    header("Location: routers.php");
    exit;
}
