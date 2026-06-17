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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mans progress — DigiMath</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/progress.css">
</head>
<body class="app-page">

    <header class="site-header">
        <a href="home.php" class="site-logo">Digi<span>Math</span></a>
        <nav><a href="profile.php">Profils</a></nav>
    </header>

    <main class="app-main progress-main">

        <a href="home.php" class="back-link">← Atpakaļ uz sākumu</a>

        <div class="page-heading">
            <h1>Mans progress</h1>
        </div>
<div class="progress-stats">
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

</main>

</body>
</html>