<?php
session_start();
require_once '../../database.php';
require_once '../../user.php';

$user_handler = new User($pdo);

if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mikrotik Management</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Mikrotik Manager</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_routers')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="routers.php">Routers</a>
                </li>
            <?php endif; ?>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_commands')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="commands.php">Commands</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pending_commands.php">Pending Commands</a>
                </li>
            <?php endif; ?>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_groups')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="groups.php">Groups</a>
                </li>
            <?php endif; ?>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_wifi')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="wifi.php">WiFi Configs</a>
                </li>
            <?php endif; ?>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_templates')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="templates.php">Templates</a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="history.php">History</a>
            </li>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">Users</a>
                </li>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>
<div class="container mt-4">
