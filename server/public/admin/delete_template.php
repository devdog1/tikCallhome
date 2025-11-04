<?php
require_once('../../database.php');

if (isset($_GET['id'])) {
    try {

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
