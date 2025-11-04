<?php
require_once('../database.php');

header('Content-Type: text/plain; version=0.0.4');

try {

    // --- Gauges for router status ---
    $adopted_count = $pdo->query("SELECT COUNT(*) FROM routers WHERE adopted = true")->fetchColumn();
    $pending_count = $pdo->query("SELECT COUNT(*) FROM routers WHERE adopted = false")->fetchColumn();
    $offline_count = $pdo->query("SELECT COUNT(*) FROM routers WHERE adopted = true AND last_seen < NOW() - INTERVAL '5 minutes'")->fetchColumn();

    echo "# TYPE router_status gauge\n";
    echo "router_status{state=\"adopted\"} $adopted_count\n";
    echo "router_status{state=\"pending\"} $pending_count\n";
    echo "router_status{state=\"offline\"} $offline_count\n";

    // --- Counter for command statuses ---
    $success_count = $pdo->query("SELECT COUNT(*) FROM router_commands WHERE status = 'success'")->fetchColumn();
    $failure_count = $pdo->query("SELECT COUNT(*) FROM router_commands WHERE status = 'failure'")->fetchColumn();
    $delivered_count = $pdo->query("SELECT COUNT(*) FROM router_commands WHERE status = 'delivered'")->fetchColumn();

    echo "# TYPE command_executions_total counter\n";
    echo "command_executions_total{status=\"success\"} $success_count\n";
    echo "command_executions_total{status=\"failure\"} $failure_count\n";
    echo "command_executions_total{status=\"delivered\"} $delivered_count\n";

} catch (PDOException $e) {
    // In a real application, you'd log this error instead of outputting it
    echo "# ERROR: Could not connect to the database.\n";
}
