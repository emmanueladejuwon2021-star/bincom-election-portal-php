<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/db.php';

try {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if (strlen($q) < 1) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    $term = '%' . $q . '%';
    $sql = "SELECT 
              pu.uniqueid,
              pu.polling_unit_name,
              pu.polling_unit_number,
              pu.polling_unit_description,
              l.lga_name,
              w.ward_name,
              (SELECT COUNT(*) FROM announced_pu_results r 
               WHERE r.polling_unit_uniqueid = pu.uniqueid OR CAST(r.polling_unit_uniqueid AS CHAR) = CAST(pu.uniqueid AS CHAR)) as has_results
            FROM polling_unit pu
            LEFT JOIN lga l ON pu.lga_id = l.lga_id
            LEFT JOIN ward w ON pu.ward_id = w.ward_id
            WHERE pu.polling_unit_name LIKE ? 
               OR pu.polling_unit_number LIKE ? 
               OR pu.polling_unit_description LIKE ?
               OR l.lga_name LIKE ?
               OR CAST(pu.uniqueid AS CHAR) = ?
            ORDER BY has_results DESC, pu.polling_unit_name ASC
            LIMIT 12";

    $results = fetchAll($sql, [$term, $term, $term, $term, $q]);
    echo json_encode(['success' => true, 'data' => $results]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An internal error occurred. Please try again later.']);
}
