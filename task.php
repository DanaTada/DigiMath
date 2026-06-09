<?php
session_start();
require 'ai_helper.php';

// Load task by ID from JSON - ONLY on first load (no POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    
    // Load from JSON
    $json_file = __DIR__ . '/uzdevumi.json';
    if (file_exists($json_file)) {
        $content = file_get_contents($json_file);
        $data = json_decode($content, true);
        $all_tasks = $data['tasks'] ?? [];
        
        // Find the task by ID
        foreach ($all_tasks as $task) {
            if ($task['id'] == $task_id) {
                $_SESSION['current_task'] = $task;
                $_SESSION['uzdevums'] = $task['text'];
                $_SESSION['pareiza'] = $task['atbilde'];
                $_SESSION['current_grade'] = $task['grade'];
                break;
            }
        }
    }
    unset($_SESSION['pēdējā_atbilde']);
    unset($_SESSION['rezultats']);
    unset($_SESSION['paskaidrojums']);
}

// If no task loaded, redirect back
if (!isset($_SESSION['uzdevums'])) {
    header('Location: taskdesk.php');
    exit;
}

$paskaidrojums = $_SESSION['paskaidrojums'] ?? '';
$rezultats     = $_SESSION['rezultats'] ?? '';
$ievaditā      = $_SESSION['pēdējā_atbilde'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $darbiba = $_POST['darbiba'] ?? '';
    $skolena = isset($_POST['atbilde']) ? trim($_POST['atbilde']) : '';

    if ($darbiba === 'atbildet') {
        $_SESSION['pēdējā_atbilde'] = $skolena;
        $ievaditā = $skolena;
        
        if ($skolena === '') {
            $rezultats = 'Lūdzu, ievadi atbildi.';
        } elseif ($skolena === trim($_SESSION['pareiza'])) {
            $rezultats = 'Pareizi!';
        } else {
            $rezultats = 'Nepareizi.';
        }
        $_SESSION['rezultats'] = $rezultats;
        
    } elseif ($darbiba === 'paskaidrot') {
        $_SESSION['pēdējā_atbilde'] = $skolena;
        $ievaditā = $skolena;
        
        $paskaidrojums = paskaidro_atbildi(
            $_SESSION['uzdevums'] ?? '',
            $_SESSION['pareiza'] ?? '',
            $skolena
        );
        $_SESSION['paskaidrojums'] = $paskaidrojums;
        
    } elseif ($darbiba === 'jauns') {
        // Generate NEW task using AI
        $current_task_text = $_SESSION['uzdevums'];
        $current_grade = $_SESSION['current_grade'];
        
        $new_task = genere_uzdevums_similar($current_task_text, $current_grade);
        
        if ($new_task && isset($new_task['uzdevums'], $new_task['atbilde'])) {
            $_SESSION['uzdevums'] = $new_task['uzdevums'];
            $_SESSION['pareiza'] = $new_task['atbilde'];
            $_SESSION['current_grade'] = $current_grade;
        }
        
        $_SESSION['pēdējā_atbilde'] = '';
        unset($_SESSION['rezultats']);
        unset($_SESSION['paskaidrojums']);
        
        // Redirect to remove ?id from URL
        header('Location: task.php');
        exit;
        
    } elseif ($darbiba === 'jauna_tema') {
        // Clear session and go back
        unset($_SESSION['uzdevums']);
        unset($_SESSION['pareiza']);
        unset($_SESSION['pēdējā_atbilde']);
        unset($_SESSION['rezultats']);
        unset($_SESSION['paskaidrojums']);
        header('Location: taskdesk.php');
        exit;
    }
}

$uzdevums = $_SESSION['uzdevums'] ?? 'Uzdevums nav pieejams';
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
        <button type="submit" name="darbiba" value="jauna_tema">Cita tēma</button>
    </form>

    <p><?= htmlspecialchars($rezultats) ?></p>
    <p><?= nl2br(htmlspecialchars($paskaidrojums)) ?></p>

    <a href="taskdesk.php">Atpakaļ</a>

</body>
</html>