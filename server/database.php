<?php
require_once('config.php');

try {
    if ($dbType === 'mysql') {
        $dsn = "mysql:host=$dbHost;dbname=$dbName";
    } elseif ($dbType === 'pgsql') {
        $dsn = "pgsql:host=$dbHost;dbname=$dbName";
    } else {
        die("Unsupported database type: $dbType");
    }

    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
