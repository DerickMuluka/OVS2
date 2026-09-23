<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireVoter();

$election_id = (int)($_GET['election_id'] ?? 0);
if (!$election_id) {
    $row = $conn->query("SELECT election_id FROM election WHERE status='active' LIMIT 1")->fetch_assoc();
    $election_id = $row['election_id'] ?? 0;
}

$election = $conn->query("SELECT * FROM election WHERE election_id=$election_id")->fetch_assoc();

// Get totals
$stmt = $conn->prepare("SELECT candidate_name, party, total_votes FROM v_candidate_totals WHERE election_id=? ORDER BY total_votes DESC");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalVotes = array_sum(array_column($results, 'total_votes'));

$pageTitle = 'Live Results';
$basePath  = '../';
$useCharts = true;
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($_GET['voted'])): ?>
    <div class="alert alert-success">✔ Your vote has been recorded. Thank you!</div>
<?php endif; ?>

<h1 style="color:var(--primary-dark); margin-bottom:8px;">📊 Live Results</h1>
<p style="color:var(--muted); margin-bottom:24px;">
    <?= htmlspecialchars($election['title'] ?? 'Election') ?> — Total votes cast: <b><?= $totalVotes ?></b>
</p>

<div class="stats-grid">
    <?php foreach ($results as $r): ?>
        <div class="stat-card">
            <div class="stat-label"><?= htmlspecialchars($r['party']) ?></div>
            <div class="stat-value"><?= $r['total_votes'] ?></div>
            <div style="margin-top:6px; font-size:0.9rem;"><?= htmlspecialchars($r['candidate_name']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="chart-grid">
    <div class="chart-box">
        <h3>Votes per Candidate (Bar)</h3>
        <canvas id="barChart"></canvas>
    </div>
    <div class="chart-box">
        <h3>Vote Share (Doughnut)</h3>
        <canvas id="pieChart"></canvas>
    </div>
</div>

<div class="card" style="margin-top:24px;">
    <h2>Detailed Breakdown</h2>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>Rank</th><th>Candidate</th><th>Party</th><th>Votes</th><th>Share</th></tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($results as $r):
                    $pct = $totalVotes ? round($r['total_votes']*100/$totalVotes, 1) : 0; ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($r['candidate_name']) ?></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($r['party']) ?></span></td>
                        <td><b><?= $r['total_votes'] ?></b></td>
                        <td><?= $pct ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => loadCharts(<?= $election_id ?>));
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>