<?php
session_start();
include 'connect_to_db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userGrade = $_SESSION['klase'];

// Get completed tasks
$stmt = $conn->prepare("SELECT uzd_completed FROM user_info WHERE user_ID = ?");
$stmt->execute([$userId]);
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();

$completedTasks = [];
if (!empty($userInfo['uzd_completed'])) {
    $completedTasks = explode(',', $userInfo['uzd_completed']);
    $completedTasks = array_map('trim', $completedTasks);
}

// Load tasks
$json = file_get_contents('uzdevumi.json');
$data = json_decode($json, true);
$allTasks = $data['tasks'];

// Calculate progress for grades 1-9
$grades = [1,2,3,4,5,6,7,8,9];
$progressData = [];

foreach ($grades as $grade) {
    $totalTasks = 0;
    $completedCount = 0;
    
    foreach ($allTasks as $task) {
        if ($task['grade'] == $grade) {
            $totalTasks++;
            
            // AUTO-COMPLETE if grade is LOWER than user's grade
            if ($grade < $userGrade) {
                $completedCount++; // Auto-complete all lower grades
            }
            // For user's grade, check actual completion
            elseif ($grade == $userGrade) {
                if (in_array($task['id'], $completedTasks)) {
                    $completedCount++;
                }
            }
            // Higher grades stay at 0
        }
    }
    
    $percentage = $totalTasks > 0 ? ($completedCount / $totalTasks) * 100 : 0;
    
    $progressData[$grade] = [
        'total' => $totalTasks,
        'completed' => $completedCount,
        'percentage' => round($percentage, 1)
    ];
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Mans Progress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .grade-item {
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
        }
        .grade-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .bar-container {
            background-color: #ddd;
            height: 30px;
            border-radius: 5px;
            overflow: hidden;
        }
        .bar-fill {
            background-color: #4caf50;
            height: 100%;
            line-height: 30px;
            color: white;
            text-align: center;
            font-size: 14px;
        }
        .auto .bar-fill {
            background-color: #2196f3;
        }
        .locked .bar-fill {
            background-color: #999;
        }
        h1 {
            color: #333;
        }
        .stats {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2196f3;
        }
    </style>
</head>
<body>

<h1>📊 Mans Progress</h1>

<div class="stats">
    <strong>Tava klase:</strong> <?= $userGrade ?>. klase<br>
    <small>✅ 1.-<?= $userGrade-1 ?>. klase: Automātiski pabeigti</small><br>
    <small>📝 <?= $userGrade ?>. klase: Jāpabeidz</small><br>
    <small>🔒 <?= $userGrade+1 ?>.-9. klase: Bloķēti</small>
</div>

<?php foreach($progressData as $grade => $data): 
    $statusClass = '';
    if ($grade < $userGrade) $statusClass = 'auto';
    if ($grade > $userGrade) $statusClass = 'locked';
?>
<div class="grade-item <?= $statusClass ?>">
    <div class="grade-label">
        <?= $grade ?>. klase (<?= $data['completed'] ?>/<?= $data['total'] ?> uzdevumi)
    </div>
    <div class="bar-container">
        <div class="bar-fill" style="width: <?= $data['percentage'] ?>%;">
            <?= $data['percentage'] ?>%
        </div>
    </div>
    <?php if($grade < $userGrade): ?>
        <small>✅ Automātiski pabeigts (reģistrēts <?= $userGrade ?>. klasē)</small>
    <?php elseif($grade > $userGrade): ?>
        <small>🔒 Bloķēts - jāpabeidz <?= $userGrade ?>. klase</small>
    <?php else: ?>
        <small>📝 Pašreizējā klase - jāizpilda atlikušie uzdevumi</small>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<p><a href="home.php" class="back-link">← Atpakaļ uz sākumu</a></p>

</body>
</html>