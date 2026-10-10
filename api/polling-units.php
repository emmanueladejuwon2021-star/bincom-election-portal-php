<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/db.php';

try {
    $wardId = isset($_GET['ward_id']) ? (int)$_GET['ward_id'] : 0;
    $lgaId = isset($_GET['lga_id']) ? (int)$_GET['lga_id'] : 0;

    $sql = "SELECT uniqueid, polling_unit_id, ward_id, lga_id, polling_unit_number, polling_unit_name 
            FROM polling_unit WHERE 1=1";
    $params = [];

    if ($wardId) {
        $sql .= " AND ward_id = ?";
        $params[] = $wardId;
    }
    if ($lgaId) {
        $sql .= " AND lga_id = ?";
        $params[] = $lgaId;
    }

    $sql .= " ORDER BY polling_unit_name ASC";

    $units = fetchAll($sql, $params);
    echo json_encode(['success' => true, 'data' => $units]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An internal error occurred. Please try again later.']);
}
