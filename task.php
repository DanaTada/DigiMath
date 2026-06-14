<?php
session_start();
require 'ai_helper.php';

if (isset($_GET['id'])) {
    $_SESSION['tema'] = ($_GET['id'] === '2') ? 'minus' : 'plus';
    unset($_SESSION['uzdevums']);
}
if (!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'plus';
}

$paskaidrojums  = '';
$rezultats      = '';
$rezultats_tips = 'info';
$ievaditā       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $darbiba = isset($_POST['darbiba']) ? $_POST['darbiba'] : '';
    $skolena = isset($_POST['atbilde']) ? trim($_POST['atbilde']) : '';

    if ($darbiba === 'atbildet') {
        $ievaditā = $skolena;
        if ($skolena === '') {
            $rezultats = 'Lūdzu, ievadi atbildi.';
            $rezultats_tips = 'info';
        } elseif ($skolena === trim($_SESSION['pareiza'])) {
            $rezultats = '✅ Pareizi! Lieliski!';
            $rezultats_tips = 'correct';
        } else {
            $rezultats = '❌ Nepareizi. Mēģini vēlreiz vai lūdz paskaidrojumu.';
            $rezultats_tips = 'wrong';
        }
    } elseif ($darbiba === 'paskaidrot') {
        $paskaidrojums = paskaidro_atbildi($_SESSION['uzdevums'], $_SESSION['pareiza'], $skolena);
    } elseif ($darbiba === 'jauns') {
        $jauns = genere_uzdevums($_SESSION['tema']);
        $_SESSION['uzdevums'] = $jauns['uzdevums'];
        $_SESSION['pareiza']  = $jauns['atbilde'];
    } elseif ($darbiba === 'jauna_tema') {
        $_SESSION['tema'] = ($_SESSION['tema'] === 'plus') ? 'minus' : 'plus';
        $jauns = genere_uzdevums($_SESSION['tema']);
        $_SESSION['uzdevums'] = $jauns['uzdevums'];
        $_SESSION['pareiza']  = $jauns['atbilde'];
    }
}

if (!isset($_SESSION['uzdevums'])) {
    $jauns = genere_uzdevums($_SESSION['tema']);
    $_SESSION['uzdevums'] = $jauns['uzdevums'];
    $_SESSION['pareiza']  = $jauns['atbilde'];
}

$uzdevums         = $_SESSION['uzdevums'];
$tema_label       = ($_SESSION['tema'] === 'plus') ? '➕ Saskaitīšana' : '➖ Atņemšana';
$jauna_tema_label = ($_SESSION['tema'] === 'plus') ? 'Pārslēgties uz atņemšanu' : 'Pārslēgties uz saskaitīšanu';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uzdevums — DigiMath</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/task.css">
</head>
<body class="app-page">

    <header class="site-header">
        <a href="home.php" class="site-logo">Digi<span>Math</span></a>
        <nav><a href="taskdesk.php">Uzdevumi</a></nav>
    </header>

    <main class="app-main">
        <div class="task-page-inner">

            <a href="taskdesk.php" class="back-link">← Atpakaļ uz uzdevumiem</a>

            <div class="task-box">
                <div class="task-box-header">
                    <div class="task-tema-badge"><?= htmlspecialchars($tema_label) ?></div>
                    <div class="task-question"><?= htmlspecialchars($uzdevums) ?></div>
                </div>

                <div class="task-box-body">
                    <form method="post">

                        <div class="answer-row">
                            <input type="text" name="atbilde"
                                   value="<?= htmlspecialchars($ievaditā) ?>"
                                   placeholder="Tava atbilde…" autocomplete="off" autofocus>
                            <button type="submit" name="darbiba" value="atbildet" class="btn-primary">
                                Atbildēt
                            </button>
                        </div>

                        <?php if ($rezultats): ?>
                            <div class="result-box <?= $rezultats_tips ?>">
                                <?= htmlspecialchars($rezultats) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($paskaidrojums): ?>
                            <div class="explanation-box">
                                <strong>🤖 MI paskaidrojums</strong>
                                <?= nl2br(htmlspecialchars($paskaidrojums)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="task-actions">
                            <button type="submit" name="darbiba" value="jauns" class="btn-secondary">
                                🔄 Jauns uzdevums
                            </button>
                            <button type="submit" name="darbiba" value="paskaidrot" class="btn-secondary">
                                🤖 Paskaidrojums
                            </button>
                            <button type="submit" name="darbiba" value="jauna_tema" class="btn-secondary" style="grid-column:1/-1;">
                                🔀 <?= htmlspecialchars($jauna_tema_label) ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </main>

</body>
</html>