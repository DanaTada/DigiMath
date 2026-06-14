<?php
session_start();
require 'ai_helper.php';
include 'connect_to_db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userGrade = $_SESSION['klase'];

// Load JSON tasks
$json = file_get_contents('uzdevumi.json');
$data = json_decode($json, true);
$allTasks = $data['tasks'];

// Get user's completed tasks
$stmt = $conn->prepare("SELECT uzd_completed FROM user_info WHERE user_ID = ?");
$stmt->execute([$userId]);
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();
$completedTasks = [];
if (!empty($userInfo['uzd_completed'])) {
    $completedTasks = explode(',', $userInfo['uzd_completed']);
    $completedTasks = array_map('trim', $completedTasks);
}

// Function to get next incomplete task for CURRENT GRADE only
function getNextIncompleteTask($allTasks, $completedTasks, $userGrade) {
    foreach($allTasks as $task) {
        if($task['grade'] == $userGrade && !in_array($task['id'], $completedTasks)) {
            return $task;
        }
    }
    return null;
}

// Function to get all available tasks (all grades up to user's grade)
function getAllAvailableTasks($allTasks, $userGrade) {
    $available = [];
    foreach($allTasks as $task) {
        if($task['grade'] <= $userGrade) {
            $available[] = $task;
        }
    }
    return $available;
}

// Function to check if ALL tasks in a grade are completed
function isGradeComplete($allTasks, $completedTasks, $grade) {
    $totalInGrade = 0;
    $completedInGrade = 0;
    foreach($allTasks as $task) {
        if($task['grade'] == $grade) {
            $totalInGrade++;
            if(in_array($task['id'], $completedTasks)) {
                $completedInGrade++;
            }
        }
    }
    return ($totalInGrade > 0 && $completedInGrade == $totalInGrade);
}

// Function to upgrade to next grade
function upgradeToNextGrade($allTasks, $completedTasks, $currentGrade, $conn, $userId) {
    $nextGrade = $currentGrade + 1;
    
    // Check if next grade has any tasks
    $hasTasksInNextGrade = false;
    foreach($allTasks as $task) {
        if($task['grade'] == $nextGrade) {
            $hasTasksInNextGrade = true;
            break;
        }
    }
    
    if($hasTasksInNextGrade && $nextGrade <= 9) {
        // Update user's grade in database
        $update = $conn->prepare("UPDATE user_info SET grade = ? WHERE user_ID = ?");
        $update->execute([$nextGrade, $userId]);
        $_SESSION['klase'] = $nextGrade;
        return $nextGrade;
    }
    return $currentGrade;
}

// Function to generate random local task (fallback)
function generateRandomLocalTask($baseTask = null) {
    $num1 = rand(1, 100);
    $num2 = rand(1, 100);
    $operators = ['+', '-', '×'];
    $op = $operators[array_rand($operators)];
    
    if ($op === '+') {
        return [
            'uzdevums' => "$num1 + $num2 =",
            'atbilde' => (string)($num1 + $num2)
        ];
    } elseif ($op === '-') {
        if ($num1 < $num2) {
            $temp = $num1;
            $num1 = $num2;
            $num2 = $temp;
        }
        return [
            'uzdevums' => "$num1 - $num2 =",
            'atbilde' => (string)($num1 - $num2)
        ];
    } else {
        $num1 = rand(1, 12);
        $num2 = rand(1, 12);
        return [
            'uzdevums' => "$num1 × $num2 =",
            'atbilde' => (string)($num1 * $num2)
        ];
    }
}

// Initialize mode
if (!isset($_SESSION['mode'])) {
    $_SESSION['mode'] = 'json';
}

// Handle POST requests
$needsNewTask = false;
$paskaidrojums = '';
$rezultats = '';
$rezultats_tips = 'info';
$ievadita = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $darbiba = isset($_POST['darbiba']) ? $_POST['darbiba'] : '';
    $skolena = isset($_POST['atbilde']) ? trim($_POST['atbilde']) : '';
    $ievadita = $skolena;
    
    if ($darbiba === 'atbildet') {
        if ($skolena === '') {
            $rezultats = 'Lūdzu, ievadi atbildi.';
            $rezultats_tips = 'info';
        } elseif ($skolena === trim($_SESSION['pareiza'])) {
            $rezultats = '✅ Pareizi! Lieliski!';
            $rezultats_tips = 'correct';
            
            if ($_SESSION['mode'] === 'json' && isset($_SESSION['current_task_id'])) {
                $taskId = $_SESSION['current_task_id'];
                
                if (!in_array($taskId, $completedTasks)) {
                    $completedTasks[] = $taskId;
                    $newCompleted = implode(',', $completedTasks);
                    $update = $conn->prepare("UPDATE user_info SET uzd_completed = ? WHERE user_ID = ?");
                    $update->execute([$newCompleted, $userId]);
                    $rezultats .= ' ✅ Progress saglabāts!';
                    
                    // Check if ALL tasks in current grade are completed
                    if (isGradeComplete($allTasks, $completedTasks, $userGrade)) {
                        // Try to upgrade to next grade
                        $newGrade = upgradeToNextGrade($allTasks, $completedTasks, $userGrade, $conn, $userId);
                        if ($newGrade != $userGrade) {
                            $userGrade = $newGrade;
                            $rezultats = "🎉 Apsveicam! Tu esi pabeidzis visus " . ($newGrade - 1) . ". klases uzdevumus un pārcelts uz {$newGrade}. klasi!";
                            $rezultats_tips = 'success';
                            // Clear current task to load new grade task
                            unset($_SESSION['current_task_id']);
                            $needsNewTask = true;
                        } else {
                            // No more grades, all tasks completed everywhere
                            $_SESSION['all_completed'] = true;
                            $rezultats = "🏆 Apsveicam! Tu esi pabeidzis VISUS uzdevumus no 1. līdz 9. klasei!";
                            $rezultats_tips = 'success';
                        }
                    }
                }
            } elseif ($_SESSION['mode'] === 'ai') {
                $rezultats .= ' (AI prakse - neskaitās progresā)';
            }
            
            $needsNewTask = true;
            $ievadita = ''; // Clear answer field
        } else {
            $rezultats = '❌ Nepareizi. Mēģini vēlreiz.';
            $rezultats_tips = 'wrong';
            $ievadita = ''; // Clear answer field on wrong answer too
        }
    } elseif ($darbiba === 'jauns') {
        $needsNewTask = true;
        $ievadita = '';
    } elseif ($darbiba === 'paskaidrot') {
        $paskaidrojums = paskaidro_atbildi($_SESSION['uzdevums'], $_SESSION['pareiza'], $skolena);
    } elseif ($darbiba === 'ai_prakse') {
        $_SESSION['mode'] = 'ai';
        if (isset($_SESSION['current_task_id'])) {
            $currentJsonTask = null;
            foreach($allTasks as $task) {
                if($task['id'] == $_SESSION['current_task_id']) {
                    $currentJsonTask = $task;
                    break;
                }
            }
            if ($currentJsonTask) {
                $aiTask = genere_lidzigu_uzdevumu($currentJsonTask);
                if ($aiTask && isset($aiTask['uzdevums'], $aiTask['atbilde'])) {
                    $_SESSION['uzdevums'] = $aiTask['uzdevums'];
                    $_SESSION['pareiza'] = $aiTask['atbilde'];
                } else {
                    $randomTask = generateRandomLocalTask($currentJsonTask);
                    $_SESSION['uzdevums'] = $randomTask['uzdevums'];
                    $_SESSION['pareiza'] = $randomTask['atbilde'];
                }
                $_SESSION['ai_base_task_id'] = $_SESSION['current_task_id'];
            } else {
                $randomTask = generateRandomLocalTask();
                $_SESSION['uzdevums'] = $randomTask['uzdevums'];
                $_SESSION['pareiza'] = $randomTask['atbilde'];
            }
        } else {
            $randomTask = generateRandomLocalTask();
            $_SESSION['uzdevums'] = $randomTask['uzdevums'];
            $_SESSION['pareiza'] = $randomTask['atbilde'];
        }
        $_SESSION['current_task_id'] = null;
        $rezultats = '🤖 AI prakses režīms - veicot uzdevumus, progress netiek saglabāts!';
        $rezultats_tips = 'info';
        $ievadita = '';
    } elseif ($darbiba === 'atpakal_json') {
        $_SESSION['mode'] = 'json';
        $needsNewTask = true;
        $rezultats = '📚 Atgriezies pie pamata uzdevumiem - progress tiks saglabāts!';
        $rezultats_tips = 'info';
        $ievadita = '';
    } elseif ($darbiba === 'jauns_ai') {
        // Generate NEW AI task - ALWAYS create a fresh task
        if (isset($_SESSION['ai_base_task_id'])) {
            $baseTask = null;
            foreach($allTasks as $task) {
                if($task['id'] == $_SESSION['ai_base_task_id']) {
                    $baseTask = $task;
                    break;
                }
            }
            if ($baseTask) {
                $aiTask = genere_lidzigu_uzdevumu($baseTask);
                if ($aiTask && isset($aiTask['uzdevums'], $aiTask['atbilde'])) {
                    $_SESSION['uzdevums'] = $aiTask['uzdevums'];
                    $_SESSION['pareiza'] = $aiTask['atbilde'];
                    $rezultats = '🔄 Jauns AI uzdevums ģenerēts!';
                    $rezultats_tips = 'info';
                } else {
                    $randomTask = generateRandomLocalTask($baseTask);
                    $_SESSION['uzdevums'] = $randomTask['uzdevums'];
                    $_SESSION['pareiza'] = $randomTask['atbilde'];
                    $rezultats = '🔄 Jauns uzdevums ģenerēts!';
                    $rezultats_tips = 'info';
                }
            } else {
                $randomTask = generateRandomLocalTask();
                $_SESSION['uzdevums'] = $randomTask['uzdevums'];
                $_SESSION['pareiza'] = $randomTask['atbilde'];
                $rezultats = '🔄 Jauns uzdevums ģenerēts!';
                $rezultats_tips = 'info';
            }
        } else {
            $randomTask = generateRandomLocalTask();
            $_SESSION['uzdevums'] = $randomTask['uzdevums'];
            $_SESSION['pareiza'] = $randomTask['atbilde'];
            $rezultats = '🔄 Jauns uzdevums ģenerēts!';
            $rezultats_tips = 'info';
        }
        $ievadita = '';
    } elseif ($darbiba === 'izveleties_uzdevumu') {
        $selectedTaskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
        $selectedTask = null;
        foreach($allTasks as $task) {
            if($task['id'] == $selectedTaskId && $task['grade'] <= $userGrade) {
                $selectedTask = $task;
                break;
            }
        }
        if ($selectedTask) {
            $_SESSION['uzdevums'] = $selectedTask['text'];
            $_SESSION['pareiza'] = $selectedTask['atbilde'];
            $_SESSION['current_task_id'] = $selectedTask['id'];
            $_SESSION['current_task_grade'] = $selectedTask['grade'];
            $_SESSION['mode'] = 'json';
            $rezultats = '📚 Ielādēts uzdevums #' . $selectedTaskId;
            $rezultats_tips = 'info';
            $ievadita = '';
        }
    }
}

// Refresh user grade from session (might have been upgraded)
$userGrade = $_SESSION['klase'];

// Load new JSON task if needed
if ($_SESSION['mode'] === 'json' && ($needsNewTask || !isset($_SESSION['current_task_id']))) {
    $nextTask = getNextIncompleteTask($allTasks, $completedTasks, $userGrade);
    
    if ($nextTask) {
        $_SESSION['uzdevums'] = $nextTask['text'];
        $_SESSION['pareiza'] = $nextTask['atbilde'];
        $_SESSION['current_task_id'] = $nextTask['id'];
        $_SESSION['current_task_grade'] = $nextTask['grade'];
        unset($_SESSION['all_completed']);
    } else {
        // No tasks in current grade - check if we can upgrade
        if (isGradeComplete($allTasks, $completedTasks, $userGrade)) {
            $newGrade = upgradeToNextGrade($allTasks, $completedTasks, $userGrade, $conn, $userId);
            if ($newGrade != $userGrade) {
                $userGrade = $newGrade;
                $nextTask = getNextIncompleteTask($allTasks, $completedTasks, $userGrade);
                if ($nextTask) {
                    $_SESSION['uzdevums'] = $nextTask['text'];
                    $_SESSION['pareiza'] = $nextTask['atbilde'];
                    $_SESSION['current_task_id'] = $nextTask['id'];
                    $_SESSION['current_task_grade'] = $nextTask['grade'];
                    $rezultats = "🎉 Apsveicam! Pārcelts uz {$userGrade}. klasi!";
                    $rezultats_tips = 'success';
                }
            } else {
                $_SESSION['all_completed'] = true;
            }
        } else {
            $_SESSION['all_completed'] = true;
        }
    }
}

$uzdevums = $_SESSION['uzdevums'] ?? '';
$allCompleted = isset($_SESSION['all_completed']) && $_SESSION['all_completed'] === true;

// Calculate progress for current grade
$totalCurrentGradeTasks = 0;
$completedCurrentGradeTasks = 0;
foreach($allTasks as $task) {
    if($task['grade'] == $userGrade) {
        $totalCurrentGradeTasks++;
        if(in_array($task['id'], $completedTasks)) {
            $completedCurrentGradeTasks++;
        }
    }
}
$progressPercent = $totalCurrentGradeTasks > 0 ? round(($completedCurrentGradeTasks / $totalCurrentGradeTasks) * 100) : 0;

// Get all available tasks for the selector
$availableTasks = getAllAvailableTasks($allTasks, $userGrade);
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uzdevums — DigiMath</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/task.css">
    <style>
        .task-selector {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .task-selector select {
            width: 100%;
            padding: 10px;
            background: #1a1a1a;
            color: white;
            border: 1px solid #444;
            border-radius: 5px;
            margin: 10px 0;
        }
        .completion-badge {
            background: #4caf50;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-left: 10px;
        }
        .fade-message {
            animation: fadeOut 3s ease forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; visibility: hidden; display: none; }
        }
    </style>
</head>
<body class="app-page">

    <header class="site-header">
        <a href="home.php" class="site-logo">Digi<span>Math</span></a>
        <nav>
            <a href="taskdesk.php">Uzdevumi</a>
            <a href="profile_progress.php">Mans progress</a>
        </nav>
    </header>

    <main class="app-main">
        <div class="task-page-inner">

            <a href="home.php" class="back-link">← Atpakaļ uz sākumu</a>

        

            <!-- Task Selector -->
            <div class="task-selector">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong>📋 Visi uzdevumi (1.-<?= $userGrade ?>. klase)</strong>
                    <?php if($allCompleted): ?>
                        <span class="completion-badge">✅ Visi pabeigti!</span>
                    <?php endif; ?>
                </div>
                <form method="post">
                    <select name="task_id" required>
                        <option value="">-- Izvēlies uzdevumu --</option>
                        <?php 
                        $currentGrade = 0;
                        foreach($availableTasks as $task): 
                            if($task['grade'] != $currentGrade):
                                $currentGrade = $task['grade'];
                        ?>
                                <option disabled style="font-weight: bold; color: #ffd700;">--- <?= $currentGrade ?>. klase ---</option>
                        <?php 
                            endif;
                            $isCompleted = in_array($task['id'], $completedTasks);
                            $status = $isCompleted ? '✅ ' : '⏳ ';
                        ?>
                            <option value="<?= $task['id'] ?>" <?= (isset($_SESSION['current_task_id']) && $_SESSION['current_task_id'] == $task['id']) ? 'selected' : '' ?>>
                                <?= $status ?>Uzdevums #<?= $task['id'] ?>: <?= htmlspecialchars(substr($task['text'], 0, 40)) ?>...
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="darbiba" value="izveleties_uzdevumu" class="btn-secondary" style="width: 100%; margin-top: 10px;">
                        📚 Ielādēt izvēlēto uzdevumu
                    </button>
                </form>
                <?php if($allCompleted): ?>
                    <div style="margin-top: 10px; text-align: center; color: #4caf50;">
                        🎉 Apsveicam! Tu esi pabeidzis visus uzdevumus no 1. līdz 9. klasei!
                    </div>
                <?php endif; ?>
            </div>

            <div class="task-box">
                <div class="task-box-header">
                    <div class="task-tema-badge">
                        <?php if($_SESSION['mode'] === 'json' && isset($_SESSION['current_task_id'])): ?>
                            📝 Uzdevums
                        <?php elseif($_SESSION['mode'] === 'ai'): ?>
                            🤖 AI ģenerēts uzdevums
                        <?php endif; ?>
                    </div>
                    <div class="task-question"><?= htmlspecialchars($uzdevums) ?></div>
                </div>

                <div class="task-box-body">
                    <form method="post" id="taskForm">

                        <div class="answer-row">
                            <input type="text" name="atbilde" id="answerInput"
                                   value="<?= htmlspecialchars($ievadita) ?>"
                                   placeholder="Tava atbilde…" autocomplete="off" autofocus>
                            <button type="submit" name="darbiba" value="atbildet" class="btn-primary">
                                Atbildēt
                            </button>
                        </div>

                        <?php if ($rezultats): ?>
                            <div id="resultMessage" class="result-box <?= $rezultats_tips ?> fade-message">
                                <?= htmlspecialchars($rezultats) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($paskaidrojums): ?>
                            <div id="explanationMessage" class="explanation-box ">
                                <strong>🤖 MI paskaidrojums</strong>
                                <?= nl2br(htmlspecialchars($paskaidrojums)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="task-actions">
                            <?php if($_SESSION['mode'] === 'json'): ?>
                                <button type="submit" name="darbiba" value="jauns" class="btn-secondary">
                                    🔄 Nākamais uzdevums
                                </button>
                                <button type="submit" name="darbiba" value="paskaidrot" class="btn-secondary">
                                    🤖 Paskaidrojums
                                </button>
                                <button type="submit" name="darbiba" value="ai_prakse" class="btn-secondary" style="grid-column:1/-1;">
                                    🎲 Sākt AI praksi (līdzīgi uzdevumi)
                                </button>
                            <?php else: ?>
                                <button type="submit" name="darbiba" value="jauns_ai" class="btn-secondary">
                                    🔄 Jauns AI uzdevums
                                </button>
                                <button type="submit" name="darbiba" value="paskaidrot" class="btn-secondary">
                                    🤖 Paskaidrojums
                                </button>
                                <button type="submit" name="darbiba" value="atpakal_json" class="btn-secondary">
                                    📚 Atpakaļ uz pamata uzdevumiem
                                </button>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Clear input field after form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('taskForm');
            const answerInput = document.getElementById('answerInput');
            
            // Clear the input field when page loads (if there's no value or it was submitted)
            if (answerInput && answerInput.value !== '') {
                // Keep the value only if it's from a failed submission? Better to clear
                // Actually let's clear it always on page load for fresh start
                answerInput.value = '';
            }
            
            // Fade out messages after 3 seconds
            const messages = document.querySelectorAll('.fade-message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.visibility = 'hidden';
                    message.style.display = 'none';
                }, 9000);
            });
        });
    </script>

</body>
</html>