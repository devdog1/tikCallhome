<?php
require_once('../../database.php');

if (isset($_GET['id'])) {
    try {

        $stmt = $pdo->prepare("DELETE FROM wifi_configs WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);

        header("Location: wifi.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
