<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$elections = $conn->query("SELECT * FROM election ORDER BY election_id DESC")->fetch_all(MYSQLI_ASSOC);
$selected  = (int)($_GET['election_id'] ?? ($elections[0]['election_id'] ?? 0));

$results = [];
if ($selected) {
    $stmt = $conn->prepare("SELECT candidate_name, party, total_votes FROM v_candidate_totals WHERE election_id=? ORDER BY total_votes DESC");
    $stmt->bind_param("i", $selected);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$totalVotes = array_sum(array_column($results,'total_votes'));

$pageTitle = 'Election Results';
$basePath  = '../';
$useCharts = true;
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="color:var(--primary-dark); margin-bottom:24px;">📊 Full Election Results</h1>

<div class="card">
    <form method="GET" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:240px; margin:0;">
            <label>Choose Election</label>
            <select name="election_id" onchange="this.form.submit()">
                <?php foreach ($elections as $e): ?>
                    <option value="<?= $e['election_id'] ?>" <?= $e['election_id']==$selected?'selected':'' ?>>
                        <?= htmlspecialchars($e['title']) ?> (<?= $e['status'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Votes</div>
        <div class="stat-value"><?= $totalVotes ?></div>
    </div>
    <?php foreach ($results as $r): ?>
        <div class="stat-card amber">
            <div class="stat-label"><?= htmlspecialchars($r['party']) ?></div>
            <div class="stat-value"><?= $r['total_votes'] ?></div>
            <div style="margin-top:6px; font-size:0.9rem;"><?= htmlspecialchars($r['candidate_name']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="chart-grid">
    <div class="chart-box">
        <h3>Bar Chart</h3>
        <canvas id="barChart"></canvas>
    </div>
    <div class="chart-box">
        <h3>Doughnut Chart</h3>
        <canvas id="pieChart"></canvas>
    </div>
</div>

<div class="card" style="margin-top:24px;">
    <h2>Breakdown</h2>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Rank</th><th>Candidate</th><th>Party</th><th>Votes</th><th>Share</th></tr></thead>
            <tbody>
                <?php $rank=1; foreach ($results as $r):
                    $pct = $totalVotes ? round($r['total_votes']*100/$totalVotes,1) : 0; ?>
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

<?php if ($selected): ?>
<script>
    window.addEventListener('DOMContentLoaded', () => loadCharts(<?= $selected ?>));
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>