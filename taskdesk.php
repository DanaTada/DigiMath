<?php
session_start();

// Set default grade if not set
if (!isset($_SESSION['klase'])) {
    $_SESSION['klase'] = 1;
}

// Load tasks from JSON
$json_file = __DIR__ . '/uzdevumi.json';
$tasks = [];

if (file_exists($json_file)) {
    $content = file_get_contents($json_file);
    $data = json_decode($content, true);
    $all_tasks = $data['tasks'] ?? [];
    
    // Filter tasks by current grade
    foreach ($all_tasks as $task) {
        if ($task['grade'] == $_SESSION['klase']) {
            $tasks[] = $task;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Uzdevumi - <?= $_SESSION['klase'] ?>. klase</title>
</head>
<body>

    <h1><?= $_SESSION['klase'] ?>. klases uzdevumi</h1>

    <?php if (empty($tasks)): ?>
        <p>Nav uzdevumu šai klasei.</p>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <p>
                <a href="task.php?id=<?= $task['id'] ?>">
                    Uzdevums <?= $task['id'] ?>
                </a>
            </p>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="home.php">Home</a>
</body>
</html>