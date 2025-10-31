<?php
require_once('../../config.php');

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("DELETE FROM wifi_configs WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);

        header("Location: wifi.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
