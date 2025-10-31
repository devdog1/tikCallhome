<?php
require_once('../../config.php');

if (isset($_GET['id'])) {
    $groupId = $_GET['id'];
    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Disassociate routers from the group
        $stmt = $pdo->prepare("UPDATE routers SET group_id = NULL WHERE group_id = :group_id");
        $stmt->execute(['group_id' => $groupId]);

        // Delete commands associated with the group
        $stmt = $pdo->prepare("DELETE FROM commands WHERE type = 'group' AND target = :group_id");
        $stmt->execute(['group_id' => $groupId]);

        // Delete the group
        $stmt = $pdo->prepare("DELETE FROM groups WHERE id = :id");
        $stmt->execute(['id' => $groupId]);

        header("Location: groups.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
