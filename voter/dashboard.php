<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireVoter();

$voter_id   = $_SESSION['voter_id'];
$voter_name = $_SESSION['voter_name'];

// Active election
$active = $conn->query("SELECT * FROM election WHERE status='active' LIMIT 1")->fetch_assoc();

// Check if voter already voted in this election
$hasVoted = false;
if ($active) {
    $stmt = $conn->prepare("SELECT vote_id FROM vote WHERE voter_id=? AND election_id=?");
    $stmt->bind_param("ii", $voter_id, $active['election_id']);
    $stmt->execute();
    $hasVoted = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Candidates
$candidates = [];
if ($active && !$hasVoted) {
    $stmt = $conn->prepare("SELECT * FROM candidate WHERE election_id=?");
    $stmt->bind_param("i", $active['election_id']);
    $stmt->execute();
    $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$pageTitle = 'Voter Dashboard';
$basePath  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="color:var(--primary-dark); margin-bottom:8px;">
    Hello, <?= htmlspecialchars($voter_name) ?>
</h1>
<p style="color:var(--muted); margin-bottom:24px;">Welcome to your voting dashboard.</p>

<?php if (!$active): ?>
    <div class="card">
        <div class="alert alert-info">No active election at the moment. Please check back later.</div>
    </div>
<?php elseif ($hasVoted): ?>
    <div class="card" style="text-align:center;">
        <div style="font-size:3.5rem; margin-bottom:10px;"></div>
        <h2>Thank You for Voting!</h2>
        <p style="color:var(--muted); margin-bottom:20px;">
            You have already cast your vote in <b><?= htmlspecialchars($active['title']) ?></b>.
        </p>
        <a href="results.php" class="btn btn-primary">View Live Results</a>
    </div>
<?php else: ?>
    <div class="card">
        <h2>🗳️ <?= htmlspecialchars($active['title']) ?></h2>
        <p style="color:var(--muted); margin-bottom:8px;"><?= htmlspecialchars($active['description']) ?></p>
        <p style="color:var(--muted); font-size:0.9rem;">
            Voting ends: <b><?= date('d M Y, H:i', strtotime($active['end_date'])) ?></b>
        </p>
    </div>

    <h2 style="color:var(--primary-dark); margin-bottom:8px;">Choose Your Candidate</h2>
    <p style="color:var(--muted);">Click <b>Vote</b> next to the candidate you support. You can vote only once.</p>

    <div class="candidates-grid">
        <?php foreach ($candidates as $c): ?>
            <div class="candidate-card">
                <div class="candidate-avatar">
                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                </div>
                <h3><?= htmlspecialchars($c['name']) ?></h3>
                <div class="party"><?= htmlspecialchars($c['party']) ?></div>
                <p class="manifesto"><?= htmlspecialchars($c['manifesto']) ?></p>
                <form method="POST" action="vote.php" onsubmit="return confirm('Confirm vote for <?= htmlspecialchars($c['name']) ?>?');">
                    <input type="hidden" name="candidate_id" value="<?= $c['candidate_id'] ?>">
                    <input type="hidden" name="election_id"  value="<?= $active['election_id'] ?>">
                    <button type="submit" class="btn btn-primary btn-full">Vote</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>