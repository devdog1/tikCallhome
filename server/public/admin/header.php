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
    <a class="navbar-brand" href="index.php">Mikrotik Manager</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Dashboard</a>
            </li>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_routers') || $user_handler->hasPermission($_SESSION['user_id'], 'view_groups')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="routersDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Routers
                    </a>
                    <div class="dropdown-menu" aria-labelledby="routersDropdown">
                        <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_routers')): ?>
                            <a class="dropdown-item" href="routers.php">All Routers</a>
                        <?php endif; ?>
                        <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_groups')): ?>
                            <a class="dropdown-item" href="groups.php">Groups</a>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_commands') || $user_handler->hasPermission($_SESSION['user_id'], 'view_wifi') || $user_handler->hasPermission($_SESSION['user_id'], 'view_templates')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="configDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Configuration
                    </a>
                    <div class="dropdown-menu" aria-labelledby="configDropdown">
                        <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_commands')): ?>
                            <a class="dropdown-item" href="commands.php">Commands</a>
                            <a class="dropdown-item" href="pending_commands.php">Pending Commands</a>
                        <?php endif; ?>
                        <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_wifi')): ?>
                            <a class="dropdown-item" href="wifi.php">WiFi Configs</a>
                        <?php endif; ?>
                        <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'view_templates')): ?>
                            <a class="dropdown-item" href="templates.php">Templates</a>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="systemDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    System
                </a>
                <div class="dropdown-menu" aria-labelledby="systemDropdown">
                    <a class="dropdown-item" href="history.php">History</a>
                    <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="users.php">Users</a>
                        <a class="dropdown-item" href="role_permissions.php">Role Permissions</a>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>
<div class="container mt-4">
