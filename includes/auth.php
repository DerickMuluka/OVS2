<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireVoter() {
    if (!isset($_SESSION['voter_id'])) {
        header("Location: ../index.php?error=login_required");
        exit;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../index.php?error=login_required");
        exit;
    }
}
?>