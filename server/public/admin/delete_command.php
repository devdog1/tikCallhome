<?php
require_once('../../database.php');

if (isset($_GET['id'])) {
    try {

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
