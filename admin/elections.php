<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$msg=''; $msgType='';

// Add
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='add') {
    $stmt = $conn->prepare("INSERT INTO election (title,description,start_date,end_date,status,created_by) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sssssi", $_POST['title'], $_POST['description'], $_POST['start_date'], $_POST['end_date'], $_POST['status'], $_SESSION['admin_id']);
    if ($stmt->execute()) { $msg='Election created.'; $msgType='success'; }
    else { $msg='Error: '.$stmt->error; $msgType='error'; }
    $stmt->close();
}

// Change status
if (isset($_GET['set']) && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $set = $_GET['set'];
    if (in_array($set, ['upcoming','active','closed'])) {
        $conn->query("UPDATE election SET status='$set' WHERE election_id=$id");
    }
    header("Location: elections.php?msg=status");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM election WHERE election_id=$id");
    header("Location: elections.php?msg=deleted");
    exit;
}

$elections = $conn->query("SELECT * FROM election ORDER BY election_id DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Elections';
$basePath  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="color:var(--primary-dark); margin-bottom:24px;">📅 Manage Elections</h1>

<?php if ($msg): ?><div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success">Action completed.</div><?php endif; ?>

<div class="card">
    <h2>Create New Election</h2>
    <form method="POST" style="display:grid; gap:14px; grid-template-columns:1fr 1fr;">
        <input type="hidden" name="action" value="add">
        <div class="form-group" style="grid-column:1/-1;"><label>Title</label><input name="title" required></div>
        <div class="form-group" style="grid-column:1/-1;"><label>Description</label><textarea name="description" rows="2"></textarea></div>
        <div class="form-group"><label>Start Date &amp; Time</label><input type="datetime-local" name="start_date" required></div>
        <div class="form-group"><label>End Date &amp; Time</label><input type="datetime-local" name="end_date" required></div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="upcoming">Upcoming</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div style="grid-column:1/-1;"><button class="btn btn-primary">Create Election</button></div>
    </form>
</div>

<div class="card">
    <h2>All Elections</h2>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>ID</th><th>Title</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($elections as $e): ?>
                    <tr>
                        <td>#<?= $e['election_id'] ?></td>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($e['start_date'])) ?></td>
                        <td><?= date('d M Y H:i', strtotime($e['end_date'])) ?></td>
                        <td>
                            <span class="badge <?= $e['status']=='active'?'badge-success':($e['status']=='closed'?'badge-danger':'badge-warn') ?>">
                                <?= ucfirst($e['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="?set=active&id=<?= $e['election_id'] ?>" class="btn btn-success btn-sm">Activate</a>
                            <a href="?set=closed&id=<?= $e['election_id'] ?>" class="btn btn-sm" style="background:#6b7280;color:#fff;">Close</a>
                            <a href="?delete=<?= $e['election_id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete election and all its votes?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>