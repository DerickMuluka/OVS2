<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$msg=''; $msgType='';

// Add
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='add') {
    $stmt = $conn->prepare("INSERT INTO candidate (name,party,manifesto,photo_url,election_id) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssssi", $_POST['name'], $_POST['party'], $_POST['manifesto'], $_POST['photo_url'], $_POST['election_id']);
    if ($stmt->execute()) { $msg='Candidate added.'; $msgType='success'; }
    else { $msg='Error: '.$stmt->error; $msgType='error'; }
    $stmt->close();
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM candidate WHERE candidate_id=$id");
    header("Location: candidates.php?msg=deleted");
    exit;
}

$elections  = $conn->query("SELECT * FROM election ORDER BY election_id DESC")->fetch_all(MYSQLI_ASSOC);
$candidates = $conn->query("
    SELECT c.*, e.title AS election_title FROM candidate c
    LEFT JOIN election e ON e.election_id=c.election_id
    ORDER BY c.candidate_id DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Candidates';
$basePath  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="color:var(--primary-dark); margin-bottom:24px;">🏅 Manage Candidates</h1>

<?php if ($msg): ?><div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success">Deleted.</div><?php endif; ?>

<div class="card">
    <h2>Add New Candidate</h2>
    <form method="POST" style="display:grid; gap:14px; grid-template-columns:1fr 1fr;">
        <input type="hidden" name="action" value="add">
        <div class="form-group"><label>Name</label><input name="name" required></div>
        <div class="form-group"><label>Party</label><input name="party" required></div>
        <div class="form-group" style="grid-column:1/-1;"><label>Manifesto</label><textarea name="manifesto" rows="3"></textarea></div>
        <div class="form-group"><label>Photo URL (optional)</label><input name="photo_url" placeholder="assets/img/photo.jpg"></div>
        <div class="form-group">
            <label>Election</label>
            <select name="election_id" required>
                <?php foreach ($elections as $e): ?>
                    <option value="<?= $e['election_id'] ?>"><?= htmlspecialchars($e['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="grid-column:1/-1;"><button class="btn btn-primary">Add Candidate</button></div>
    </form>
</div>

<div class="card">
    <h2>All Candidates</h2>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>ID</th><th>Name</th><th>Party</th><th>Election</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($candidates as $c): ?>
                    <tr>
                        <td>#<?= $c['candidate_id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['party']) ?></td>
                        <td><?= htmlspecialchars($c['election_title']) ?></td>
                        <td>
                            <a href="?delete=<?= $c['candidate_id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete candidate?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>