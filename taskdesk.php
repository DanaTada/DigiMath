<?php
session_start();

// Ielādē tēmas no JSON
$VISI = json_decode(file_get_contents(__DIR__ . '/uzdevumi.json'), true);

// Skolēna klase no sesijas (vai 1, ja nav)
$klase = isset($_SESSION['klase']) ? (int)$_SESSION['klase'] : 1;
if (!isset($VISI[$klase])) { $klase = 1; }

$temas = $VISI[$klase]; // 2 tēmas šai klasei
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uzdevumi — DigiMath</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/taskdesk.css">
</head>
<body class="app-page">

    <header class="site-header">
        <a href="home.php" class="site-logo">Digi<span>Math</span></a>
        <nav><a href="profile.php">Profils</a></nav>
    </header>

    <main class="app-main">

        <a href="home.php" class="back-link">← Atpakaļ uz sākumu</a>

        <div class="page-heading">
            <h1>Uzdevumi</h1>
            <p><?= $klase ?>. klases tēmas — izvēlies un sāc risināt!</p>
        </div>

        <div class="task-grid">
            <?php foreach ($temas as $t): ?>
                <div class="task-card">
                    <div class="task-card-badge"><?= htmlspecialchars($t['icon']) ?> <?= htmlspecialchars($t['label']) ?></div>
                    <h2><?= htmlspecialchars($t['label']) ?></h2>
                    <p><?= htmlspecialchars($t['apraksts']) ?></p>
                    <a href="task.php?tema=<?= urlencode($t['id']) ?>" class="btn-primary">Sākt uzdevumu</a>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

</body>
</html>