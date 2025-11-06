<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once '../../user.php';

$user_handler = new User($pdo);

require_once 'check_permission.php';

check_permission('manage_templates');

if (isset($_GET['id'])) {
    $templateId = $_GET['id'];
    try {
        $pdo->beginTransaction();

        // Disassociate groups from the template
        $stmt = $pdo->prepare("UPDATE groups SET base_template_id = NULL WHERE base_template_id = :id");
        $stmt->execute(['id' => $templateId]);

        // Delete the template
        $stmt = $pdo->prepare("DELETE FROM config_templates WHERE id = :id");
        $stmt->execute(['id' => $templateId]);

        log_user_action($pdo, $_SESSION['user_id'], null, "Template #" . $templateId . " deleted");

        $pdo->commit();

        $_SESSION['success_message'] = "Template deleted successfully.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("Location: templates.php");
    exit;
}
