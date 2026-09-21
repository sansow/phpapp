<?php
// Readiness: can this pod serve traffic RIGHT NOW?
// When DB_REQUIRED=true (set in Module 1.8), gate readiness on SQL Server.
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$required = strtolower((string) getenv('DB_REQUIRED')) === 'true';
if (!$required) {
    echo json_encode(['status' => 'ready', 'db_checked' => false]);
    exit;
}

[$pdo, $status] = db_connect();
if ($pdo === null) {
    http_response_code(503);
    echo json_encode(['status' => 'not ready', 'db' => $status]);
    exit;
}
echo json_encode(['status' => 'ready', 'db' => 'connected']);
