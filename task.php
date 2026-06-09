<?php
session_start();
require 'ai_helper.php';

// Ja nāk no taskdesk.php (?id=1 vai ?id=2) - uzstāda tēmu un ģenerē jaunu uzdevumu
if (isset($_GET['id'])) {
    $_SESSION['tema'] = ($_GET['id'] === '2') ? 'minus' : 'plus';
    unset($_SESSION['uzdevums']);
}

if (!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'plus';
}

$paskaidrojums = '';   // AI paskaidrojums
$rezultats     = '';   // "Pareizi!" / "Nepareizi."
$ievaditā      = '';   // ko rādīt atbildes laukā

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $darbiba = isset($_POST['darbiba']) ? $_POST['darbiba'] : '';
    $skolena = isset($_POST['atbilde']) ? trim($_POST['atbilde']) : '';

    if ($darbiba === 'atbildet') {
        // Pārbauda atbildi - atbilde PALIEK laukā
        $ievaditā = $skolena;
        if ($skolena === '') {
            $rezultats = 'Lūdzu, ievadi atbildi.';
        } elseif ($skolena === trim($_SESSION['pareiza'])) {
            $rezultats = 'Pareizi!';
        } else {
            $rezultats = 'Nepareizi.';
        }
    } elseif ($darbiba === 'paskaidrot') {
        // AI paskaidrojums - lauks tiek notīrīts
        $paskaidrojums = paskaidro_atbildi(
            $_SESSION['uzdevums'],
            $_SESSION['pareiza'],
            $skolena
        );
    } elseif ($darbiba === 'jauns') {
        // Jauns uzdevums tajā pašā tēmā - lauks tiek notīrīts
        $jauns = genere_uzdevums($_SESSION['tema']);
        $_SESSION['uzdevums'] = $jauns['uzdevums'];
        $_SESSION['pareiza']  = $jauns['atbilde'];
    } elseif ($darbiba === 'jauna_tema') {
        // Cita tēma (plus <-> minus) - lauks tiek notīrīts
        $_SESSION['tema'] = ($_SESSION['tema'] === 'plus') ? 'minus' : 'plus';
        $jauns = genere_uzdevums($_SESSION['tema']);
        $_SESSION['uzdevums'] = $jauns['uzdevums'];
        $_SESSION['pareiza']  = $jauns['atbilde'];
    }
}

// Pirmajā reizē (vēl nav uzdevuma) - uzģenerē
if (!isset($_SESSION['uzdevums'])) {
    $jauns = genere_uzdevums($_SESSION['tema']);
    $_SESSION['uzdevums'] = $jauns['uzdevums'];
    $_SESSION['pareiza']  = $jauns['atbilde'];
}

$uzdevums = $_SESSION['uzdevums'];
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Uzdevums</title>
</head>
<body>

    <h1><?= htmlspecialchars($uzdevums) ?></h1>

    <form method="post">
        <input type="text" name="atbilde" value="<?= htmlspecialchars($ievaditā) ?>">
        <button type="submit" name="darbiba" value="atbildet">Atbildēt</button>
        <br><br>
        <button type="submit" name="darbiba" value="jauns">Jauns uzdevums</button>
        <button type="submit" name="darbiba" value="paskaidrot">Paskaidrojums</button>
        <button type="submit" name="darbiba" value="jauna_tema">Jauna tēma</button>
    </form>

    <p><?= htmlspecialchars($rezultats) ?></p>
    <p><?= nl2br(htmlspecialchars($paskaidrojums)) ?></p>

    <a href="taskdesk.php">Atpakaļ</a>

</body>
</html>
