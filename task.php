<?php
session_start();
require 'ai_helper.php';

// Ielādē tēmas no JSON
$VISI = json_decode(file_get_contents(__DIR__ . '/uzdevumi.json'), true);

// Skolēna klase no sesijas
$klase = isset($_SESSION['klase']) ? (int)$_SESSION['klase'] : 1;
if (!isset($VISI[$klase])) { $klase = 1; }
$temas = $VISI[$klase]; // 2 tēmas

// Atrod tēmas indeksu pēc id
function atrast_temu($temas, $tema_id) {
    foreach ($temas as $i => $t) {
        if ($t['id'] === $tema_id) return $i;
    }
    return 0;
}

// GET: izvēlēta tēma no taskdesk (?tema=ID)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tema'])) {
    $_SESSION['tema_id'] = $_GET['tema'];
    unset($_SESSION['uzdevums']);
}
// Ja tēma vēl nav izvēlēta vai neder šai klasei — ņem pirmo
if (!isset($_SESSION['tema_id'])) {
    $_SESSION['tema_id'] = $temas[0]['id'];
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
        // Pieņem gan komatu, gan punktu decimāldaļās (9,6 == 9.6)
        $atb_skolens = str_replace('.', ',', trim($skolena));
        $atb_pareiza = str_replace('.', ',', trim($_SESSION['pareiza']));
        if ($skolena === '') {
            $rezultats = 'Lūdzu, ievadi atbildi.';
            $rezultats_tips = 'info';
        } elseif ($atb_skolens === $atb_pareiza) {
            $rezultats = '✅ Pareizi! Lieliski!';
            $rezultats_tips = 'correct';
        } else {
            $rezultats = '❌ Nepareizi. Mēģini vēlreiz vai lūdz paskaidrojumu.';
            $rezultats_tips = 'wrong';
        }
    } elseif ($darbiba === 'paskaidrot') {
        $paskaidrojums = paskaidro_atbildi(
            $_SESSION['uzdevums'],
            $_SESSION['pareiza'],
            $skolena,
            $klase
        );
    } elseif ($darbiba === 'jauns') {
        unset($_SESSION['uzdevums']); // ģenerēs jaunu zemāk (tā pati tēma)
    } elseif ($darbiba === 'jauna_tema') {
        // Pārslēdzas uz OTRO šīs klases tēmu
        $idx = atrast_temu($temas, $_SESSION['tema_id']);
        $cits = $temas[1 - $idx]; // otra tēma
        $_SESSION['tema_id'] = $cits['id'];
        unset($_SESSION['uzdevums']);
    }
}

// Pašreizējā tēma
$idx  = atrast_temu($temas, $_SESSION['tema_id']);
$tema = $temas[$idx];

// Ja nav uzdevuma — ģenerē jaunu (uz piemēru bāzes)
if (!isset($_SESSION['uzdevums'])) {
    $tips = isset($tema['tips']) ? $tema['tips'] : null;
    $jauns = genere_uzdevumu($tema['label'], $klase, $tema['piemeri'], $tips);
    $_SESSION['uzdevums'] = $jauns['uzdevums'];
    $_SESSION['pareiza']  = $jauns['atbilde'];
}

$uzdevums   = $_SESSION['uzdevums'];
$tema_label = $tema['icon'] . ' ' . $tema['label'];
$cita_tema  = $temas[1 - $idx]['label'];

// Padoms par atbildes formātu (tikai daļskaitļiem un decimāldaļām)
$padoms = '';
if (($tema['tips'] ?? '') === 'decimal') {
    $padoms = 'Decimāldaļu raksti ar komatu vai punktu, piem.: 9,6 vai 9.6';
} elseif (($tema['tips'] ?? '') === 'dalskaitli') {
    $padoms = 'Daļskaitli raksti ar slīpsvītru, piem.: 1/2';
}
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
                    <div class="task-question">
                        <?php foreach (explode('|', $uzdevums) as $rinda): ?>
                            <div><?= htmlspecialchars(trim($rinda)) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="task-box-body">
                    <form method="post" action="task.php">

                        <div class="answer-row">
                            <input type="text" name="atbilde"
                                   value="<?= htmlspecialchars($ievaditā) ?>"
                                   placeholder="Tava atbilde…" autocomplete="off" autofocus>
                            <button type="submit" name="darbiba" value="atbildet" class="btn-primary">
                                Atbildēt
                            </button>
                        </div>

                        <?php if ($padoms): ?>
                            <p class="answer-hint">💡 <?= htmlspecialchars($padoms) ?></p>
                        <?php endif; ?>

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
                                🔀 Pārslēgties: <?= htmlspecialchars($cita_tema) ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </main>

</body>
</html>