<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$election_id = (int)($_GET['election_id'] ?? 0);

if (!$election_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT candidate_name, party, total_votes
    FROM v_candidate_totals
    WHERE election_id = ?
    ORDER BY total_votes DESC
");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
$stmt->close();
?>