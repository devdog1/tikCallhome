<?php
session_start();
require_once('../../database.php');
require_once('../../helpers.php');
require_once '../../user.php';

$user_handler = new User($pdo);

require_once 'check_permission.php';

check_permission('manage_wifi');

if (isset($_GET['id'])) {
    $wifiId = $_GET['id'];
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM wifi_configs WHERE id = :id");
        $stmt->execute(['id' => $wifiId]);

        log_user_action($pdo, $_SESSION['user_id'], null, "WiFi Config #" . $wifiId . " deleted");

        $pdo->commit();

        $_SESSION['success_message'] = "WiFi configuration deleted successfully.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("Location: wifi.php");
    exit;
}
