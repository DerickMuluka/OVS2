<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireVoter();

$voter_id     = $_SESSION['voter_id'];
$candidate_id = (int)($_POST['candidate_id'] ?? 0);
$election_id  = (int)($_POST['election_id']  ?? 0);

if (!$candidate_id || !$election_id) {
    header("Location: dashboard.php");
    exit;
}

// Verify no double vote
$stmt = $conn->prepare("SELECT vote_id FROM vote WHERE voter_id=? AND election_id=?");
$stmt->bind_param("ii", $voter_id, $election_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    header("Location: dashboard.php?msg=already_voted");
    exit;
}
$stmt->close();

// Insert vote
$stmt = $conn->prepare("INSERT INTO vote (voter_id,candidate_id,election_id) VALUES (?,?,?)");
$stmt->bind_param("iii", $voter_id, $candidate_id, $election_id);
$stmt->execute();
$stmt->close();

// Mark voter
$conn->query("UPDATE voter SET has_voted = TRUE WHERE voter_id = $voter_id");

header("Location: results.php?election_id=$election_id&voted=1");
exit;
?>