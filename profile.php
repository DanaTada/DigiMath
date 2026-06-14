<?php
session_start();
$initial = isset($_SESSION['uzvards']) ? mb_strtoupper(mb_substr($_SESSION['uzvards'], 0, 1)) : '?';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mans profils — DigiMath</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body class="app-page">

    <header class="site-header">
        <a href="home.php" class="site-logo">Digi<span>Math</span></a>
        <nav><a href="taskdesk.php">Uzdevumi</a></nav>
    </header>

    <main class="app-main">

        <a href="home.php" class="back-link">← Atpakaļ uz sākumu</a>

        <div class="page-heading">
            <h1>Mans profils</h1>
        </div>

        <div class="profile-card">

            <div class="profile-card-header">
                <div class="avatar"><?= htmlspecialchars($initial) ?></div>
                <div>
                    <div class="name"><?= htmlspecialchars($_SESSION['uzvards'] ?? '—') ?></div>
                    <div class="handle">@<?= htmlspecialchars($_SESSION['username'] ?? '—') ?></div>
                </div>
            </div>

            <div class="profile-fields">
                <div class="profile-field">
                    <label>Lietotājvārds</label>
                    <span><?= htmlspecialchars($_SESSION['username'] ?? '—') ?></span>
                </div>
                <div class="profile-field">
                    <label>Uzvārds</label>
                    <span><?= htmlspecialchars($_SESSION['uzvards'] ?? '—') ?></span>
                </div>
                <div class="profile-field">
                    <label>Klase</label>
                    <span><?= htmlspecialchars($_SESSION['klase'] ?? '—') ?>. klase</span>
                </div>
                <div class="profile-field">
                    <label>Skola</label>
                    <span><?= htmlspecialchars($_SESSION['skola_name'] ?? '—') ?></span>
                </div>
                <div class="profile-field">
                    <label>E-pasts</label>
                    <span><?= htmlspecialchars($_SESSION['e-pasts'] ?? '—') ?></span>
                </div>
            </div>

            <div class="profile-progress">
                <p class="progress-label">Mans progress (kopējais)</p>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill"></div>
                </div>
            </div>

        </div>

    </main>

</body>
</html>