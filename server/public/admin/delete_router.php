<?php
require_once('../../database.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
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
