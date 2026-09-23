<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Online Voting System' ?></title>
<link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= $basePath ?? '' ?>index.php" class="brand">
            <span class="brand-mark">🗳️</span> VoteFlow
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
        <nav class="site-nav" id="siteNav">
            <?php if (isset($_SESSION['voter_id'])): ?>
                <a href="<?= $basePath ?? '' ?>voter/dashboard.php">Dashboard</a>
                <a href="<?= $basePath ?? '' ?>voter/results.php">Results</a>
                <a href="<?= $basePath ?? '' ?>logout.php" class="btn-outline">Logout</a>
            <?php elseif (isset($_SESSION['admin_id'])): ?>
                <a href="<?= $basePath ?? '' ?>admin/dashboard.php">Dashboard</a>
                <a href="<?= $basePath ?? '' ?>admin/voters.php">Voters</a>
                <a href="<?= $basePath ?? '' ?>admin/candidates.php">Candidates</a>
                <a href="<?= $basePath ?? '' ?>admin/elections.php">Elections</a>
                <a href="<?= $basePath ?? '' ?>logout.php" class="btn-outline">Logout</a>
            <?php else: ?>
                <a href="<?= $basePath ?? '' ?>index.php">Login</a>
                <a href="<?= $basePath ?? '' ?>voter/register.php" class="btn-primary">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main">