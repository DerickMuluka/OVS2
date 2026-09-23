<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$totalVoters     = $conn->query("SELECT COUNT(*) AS c FROM voter")->fetch_assoc()['c'];
$verifiedVoters  = $conn->query("SELECT COUNT(*) AS c FROM voter WHERE is_verified=TRUE")->fetch_assoc()['c'];
$pendingVoters   = $totalVoters - $verifiedVoters;
$totalCandidates = $conn->query("SELECT COUNT(*) AS c FROM candidate")->fetch_assoc()['c'];
$totalElections  = $conn->query("SELECT COUNT(*) AS c FROM election")->fetch_assoc()['c'];
$totalVotes      = $conn->query("SELECT COUNT(*) AS c FROM vote")->fetch_assoc()['c'];

$recentVotes = $conn->query("
    SELECT v.voted_at, vt.full_name, c.name AS candidate, e.title AS election
    FROM vote v
    JOIN voter vt ON vt.voter_id = v.voter_id
    JOIN candidate c ON c.candidate_id = v.candidate_id
    JOIN election e ON e.election_id = v.election_id
    ORDER BY v.voted_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Admin Dashboard';
$basePath  = '../';
$useCharts = true;
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="color:var(--primary-dark); margin-bottom:24px;">Admin Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Voters</div>
        <div class="stat-value"><?= $totalVoters ?></div>
    </div>
    <div class="stat-card emerald">
        <div class="stat-label">Verified</div>
        <div class="stat-value"><?= $verifiedVoters ?></div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?= $pendingVoters ?></div>
    </div>
    <div class="stat-card rose">
        <div class="stat-label">Total Votes</div>
        <div class="stat-value"><?= $totalVotes ?></div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Elections</div>
        <div class="stat-value"><?= $totalElections ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Candidates</div>
        <div class="stat-value"><?= $totalCandidates ?></div>
    </div>
</div>

<div class="chart-grid">
    <div class="chart-box">
        <h3>Votes for Active Election</h3>
        <canvas id="barChart"></canvas>
    </div>
    <div class="chart-box">
        <h3>Vote Share</h3>
        <canvas id="pieChart"></canvas>
    </div>
</div>

<div class="card" style="margin-top:24px;">
    <h2>Recent Votes</h2>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>Time</th><th>Voter</th><th>Candidate</th><th>Election</th></tr>
            </thead>
            <tbody>
                <?php if (empty($recentVotes)): ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--muted);">No votes yet.</td></tr>
                <?php else: foreach ($recentVotes as $v): ?>
                    <tr>
                        <td><?= date('d M, H:i', strtotime($v['voted_at'])) ?></td>
                        <td><?= htmlspecialchars($v['full_name']) ?></td>
                        <td><?= htmlspecialchars($v['candidate']) ?></td>
                        <td><?= htmlspecialchars($v['election']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    (function(){
        const active = <?= json_encode(
            $conn->query("SELECT election_id FROM election WHERE status='active' LIMIT 1")->fetch_assoc()['election_id'] ?? 0
        ) ?>;
        if (active) {
            window.addEventListener('DOMContentLoaded', () => loadCharts(active));
        }
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>