<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once 'check_permission.php';

check_permission('manage_commands'); // Re-using manage_commands for groups

if (isset($_GET['id'])) {
    $groupId = $_GET['id'];
    try {
        $pdo->beginTransaction();

        // Disassociate routers from the group
        $stmt = $pdo->prepare("UPDATE routers SET group_id = NULL WHERE group_id = :group_id");
        $stmt->execute(['group_id' => $groupId]);

        // Delete commands associated with the group
        $stmt = $pdo->prepare("DELETE FROM commands WHERE type = 'group' AND target = :group_id");
        $stmt->execute(['group_id' => $groupId]);

        // Delete the group
        $stmt = $pdo->prepare("DELETE FROM groups WHERE id = :id");
        $stmt->execute(['id' => $groupId]);

        log_user_action($pdo, $_SESSION['user_id'], null, "Group #" . $groupId . " deleted");

        $pdo->commit();

        $_SESSION['success_message'] = "Group deleted successfully.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("Location: groups.php");
    exit;
}
