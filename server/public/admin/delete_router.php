<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once 'check_permission.php';

check_permission('manage_routers');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        log_user_action($pdo, $_SESSION['user_id'], $id, "Router deleted");

        // First, delete related commands to maintain referential integrity
        $stmt = $pdo->prepare("DELETE FROM router_commands WHERE router_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM command_logs WHERE router_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM routers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: routers.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: routers.php");
    exit;
}
