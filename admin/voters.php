<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Verify / Unverify
if (isset($_GET['verify'])) {
    $vid = (int)$_GET['verify'];
    $conn->query("UPDATE voter SET is_verified = TRUE WHERE voter_id = $vid");
    header("Location: voters.php?msg=verified");
    exit;
}
if (isset($_GET['unverify'])) {
    $vid = (int)$_GET['unverify'];
    $conn->query("UPDATE voter SET is_verified = FALSE WHERE voter_id = $vid");
    header("Location: voters.php?msg=unverified");
    exit;
}
if (isset($_GET['delete'])) {
    $vid = (int)$_GET['delete'];
    $conn->query("DELETE FROM voter WHERE voter_id = $vid");
    header("Location: voters.php?msg=deleted");
    exit;
}

$voters = $conn->query("SELECT * FROM voter ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Voters';
$basePath  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="color:var(--primary-dark); margin-bottom:24px;">👥 Manage Voters</h1>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success">Action completed.</div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
                    <th>Voter Card</th><th>Status</th><th>Voted</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($voters as $v): ?>
                    <tr>
                        <td>#<?= $v['voter_id'] ?></td>
                        <td><?= htmlspecialchars($v['full_name']) ?></td>
                        <td><?= htmlspecialchars($v['email']) ?></td>
                        <td><?= htmlspecialchars($v['phone']) ?></td>
                        <td><?= htmlspecialchars($v['voter_card_no']) ?></td>
                        <td>
                            <span class="badge <?= $v['is_verified'] ? 'badge-success' : 'badge-warn' ?>">
                                <?= $v['is_verified'] ? 'Verified' : 'Pending' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $v['has_voted'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $v['has_voted'] ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$v['is_verified']): ?>
                                <a href="?verify=<?= $v['voter_id'] ?>" class="btn btn-success btn-sm">Verify</a>
                            <?php else: ?>
                                <a href="?unverify=<?= $v['voter_id'] ?>" class="btn btn-sm" style="background:#6b7280;color:#fff;">Unverify</a>
                            <?php endif; ?>
                            <a href="?delete=<?= $v['voter_id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this voter?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>