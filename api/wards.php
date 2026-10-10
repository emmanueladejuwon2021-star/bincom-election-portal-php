<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/db.php';

try {
    $lgaId = isset($_GET['lga_id']) ? (int)$_GET['lga_id'] : 0;
    if (!$lgaId) {
        echo json_encode(['success' => false, 'error' => 'lga_id parameter is required']);
        exit;
    }

    $wards = fetchAll(
        "SELECT uniqueid, ward_id, ward_name, lga_id FROM ward WHERE lga_id = ? ORDER BY ward_name ASC",
        [$lgaId]
    );

    echo json_encode(['success' => true, 'data' => $wards]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An internal error occurred. Please try again later.']);
}
