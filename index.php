<?php
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        // 1. Try admin (login by username OR email)
        $stmt = $conn->prepare("SELECT admin_id, username, password FROM admin WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id']   = $row['admin_id'];
                $_SESSION['admin_name'] = $row['username'];
                header("Location: admin/dashboard.php");
                exit;
            }
        }
        $stmt->close();

        // 2. Try voter
        $stmt = $conn->prepare("SELECT voter_id, full_name, password, is_verified FROM voter WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (!password_verify($password, $row['password'])) {
                $error = 'Invalid password.';
            } elseif (!$row['is_verified']) {
                $error = 'Your account is pending admin verification.';
            } else {
                session_regenerate_id(true);
                $_SESSION['voter_id']   = $row['voter_id'];
                $_SESSION['voter_name'] = $row['full_name'];
                header("Location: voter/dashboard.php");
                exit;
            }
        } else {
            $error = 'No account found with those credentials.';
        }
        $stmt->close();
    }
}

$pageTitle = 'Login — VoteFlow';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <h1>Welcome Back</h1>
    <p class="subtitle">Sign in as <b>Voter</b> or <b>Admin</b></p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful! Wait for admin verification.</div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email or Username</label>
            <input type="text" id="email" name="email" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Sign In</button>
    </form>

    <p style="text-align:center; margin-top:20px; color:var(--muted); font-size:0.9rem;">
        New voter? <a href="voter/register.php" style="color:var(--primary); font-weight:600;">Register here</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>