<?php
require_once('../../config.php');

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Disassociate groups from the template
        $stmt = $pdo->prepare("UPDATE groups SET base_template_id = NULL WHERE base_template_id = :id");
        $stmt->execute(['id' => $_GET['id']]);

        // Delete the template
        $stmt = $pdo->prepare("DELETE FROM config_templates WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);

        header("Location: templates.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: ". $e->getMessage());
    }
}
