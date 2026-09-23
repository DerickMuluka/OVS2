<?php
require_once __DIR__ . '/../config/db.php';

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $phone     = trim($_POST['phone']);
    $dob       = $_POST['dob'];
    $voter_card= trim($_POST['voter_card_no']);

    // Duplicate check
    $stmt = $conn->prepare("SELECT voter_id FROM voter WHERE email = ? OR voter_card_no = ?");
    $stmt->bind_param("ss", $email, $voter_card);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $msg = 'Email or Voter Card Number already registered.';
        $msgType = 'error';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO voter (full_name,email,password,phone,dob,voter_card_no) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $full_name,$email,$hash,$phone,$dob,$voter_card);
        if ($stmt->execute()) {
            header("Location: ../index.php?registered=1");
            exit;
        } else {
            $msg = 'Registration failed: ' . $stmt->error;
            $msgType = 'error';
        }
    }
    $stmt->close();
}

$pageTitle = 'Voter Registration';
$basePath  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrap" style="max-width:560px;">
    <h1>Create Voter Account</h1>
    <p class="subtitle">Fill in your details to register</p>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm">Confirm Password</label>
            <input type="password" id="confirm" name="confirm" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone (07XXXXXXXX)</label>
            <input type="text" id="phone" name="phone" pattern="[0-9]{10}" required>
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" required>
        </div>
        <div class="form-group">
            <label for="voter_card_no">Voter Card Number</label>
            <input type="text" id="voter_card_no" name="voter_card_no" placeholder="e.g. VTR007" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Register</button>
    </form>

    <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
        Already have an account? <a href="../index.php" style="color:var(--primary); font-weight:600;">Login</a>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>