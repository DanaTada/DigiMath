<?php
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Uzdevumi: jautājums + pareizā atbilde
$uzdevumi = [
    '1' => ['jautajums' => '1+3=?', 'atbilde' => '4'],
    '2' => ['jautajums' => '3-1=?', 'atbilde' => '2'],
];

$uzdevums = isset($uzdevumi[$id]) ? $uzdevumi[$id]['jautajums'] : '';
$pareiza  = isset($uzdevumi[$id]) ? $uzdevumi[$id]['atbilde']   : '';

$rezultats = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atbilde = isset($_POST['atbilde']) ? trim($_POST['atbilde']) : '';
    if ($atbilde === $pareiza) {
        $rezultats = 'Pareizi!';
    } else {
        $rezultats = 'Nepareizi. Mēģini vēlreiz!';
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Uzdevums</title>
</head>
<body>

    <h1><?= $uzdevums ?></h1>

    <form action="task.php?id=<?= htmlspecialchars($id) ?>" method="post">
        <input type="text" name="atbilde" required>
        <button type="submit">Pārbaudīt</button>
    </form>

    <p><?= $rezultats ?></p>

    <a href="taskdesk.php">Atpakaļ uz uzdevumiem</a>

</body>
</html>