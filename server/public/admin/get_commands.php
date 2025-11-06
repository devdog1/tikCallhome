<?php
require_once('../../database.php');
require_once('../../helpers.php');

session_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

require_once('check_permission.php');
check_permission('manage_commands');

$type = $_POST['type'] ?? '';
$target = $_POST['target'] ?? '';

$draw = $_POST['draw'] ?? 1;
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;

$query = "SELECT * FROM commands";
$count_query = "SELECT count(*) FROM commands";
$where = [];
$params = [];

if (!empty($type)) {
    $where[] = "type = :type";
    $params[':type'] = $type;
}

if (!empty($target) && $type !== 'generic') {
    $where[] = "target = :target";
    $params[':target'] = $target;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
    $count_query .= " WHERE " . implode(" AND ", $where);
}

// Get total records
$stmt_count = $pdo->prepare($count_query);
$stmt_count->execute($params);
$records_total = $stmt_count->fetchColumn();

// Add ordering and pagination
$query .= " ORDER BY id DESC LIMIT :start, :length";
$params[':start'] = (int)$start;
$params[':length'] = (int)$length;

$stmt = $pdo->prepare($query);
foreach ($params as $key => &$val) {
    if (is_int($val)) {
        $stmt->bindParam($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
}
$stmt->execute();
$commands = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "draw" => intval($draw),
    "recordsTotal" => $records_total,
    "recordsFiltered" => $records_total,
    "data" => $commands
];

header('Content-Type: application/json');
echo json_encode($response);
