<?php 
session_start();
include 'connect_to_db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userGrade = $_SESSION['klase'];

// Get completed tasks from database
$stmt = $conn->prepare("SELECT uzd_completed FROM user_info WHERE user_ID = ?");
$stmt->execute([$userId]);
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();

$completedTasks = [];
if (!empty($userInfo['uzd_completed'])) {
    $completedTasks = explode(',', $userInfo['uzd_completed']);
    $completedTasks = array_map('trim', $completedTasks);
}

// Load tasks from JSON
$json = file_get_contents('uzdevumi.json');
$data = json_decode($json, true);
$allTasks = $data['tasks'];

// Calculate progress - LOWER GRADES ARE AUTO-COMPLETED
$totalTasks = 0;
$completedCount = 0;

foreach($allTasks as $task) {
    // Only count tasks up to user's grade
    if($task['grade'] <= $userGrade) {
        $totalTasks++;
        
        // If task grade is LOWER than user's grade -> AUTO COMPLETE (100%)
        if($task['grade'] < $userGrade) {
            $completedCount++; // Auto-complete!
        } 
        // If task grade is EQUAL to user's grade -> check actual completion
        elseif($task['grade'] == $userGrade) {
            if(in_array($task['id'], $completedTasks)) {
                $completedCount++;
            }
        }
    }
}

$progressPercent = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sākums — DigiMath</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body class="app-page">

    <header class="site-header">
        <div class="site-logo">Digi<span>Math</span></div>
        <nav>
            <a href="profile.php">Mans profils</a>
        </nav>
    </header>

    <main class="app-main">

        <div class="page-heading">
            <h1>Sveiks, <span style="color:var(--yellow);"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'skolēn' ?></span>!</h1>
            <p>Ko šodien mācīsimies?</p>
        </div>

        <!-- Quick Progress Summary -->
        <div style="background: #2a2a2a; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-weight: bold;">Tavs progress</span>
                <span><?= $completedCount ?>/<?= $totalTasks ?> uzdevumi (<?= $progressPercent ?>%)</span>
            </div>
            <div style="background: #444; height: 25px; border-radius: 12px; overflow: hidden;">
                <div style="background: var(--yellow, #ffd700); width: <?= $progressPercent ?>%; height: 100%; border-radius: 12px;"></div>
            </div>
            <div style="margin-top: 10px; font-size: 14px; color: #aaa;">
                <?= $userGrade ?>. klase | 
                <strong>1.-<?= $userGrade-1 ?>. klase:</strong> automātiski pabeigti 
            </div>
        </div>

        <div class="home-grid">

            <a href="profile.php" class="nav-card">
                <div class="nav-card-icon">👤</div>
                <h2>Mans profils</h2>
                <p>Sava konta informācija un personas dati.</p>
            </a>

            <a href="profile_progress.php" class="nav-card">
                <div class="nav-card-icon">📈</div>
                <h2>Mans progress</h2>
                <p>Apskati, cik uzdevumi pabeigti un kas vēl jāapgūst.</p>
            </a>

            <a href="task.php" class="nav-card">
                <div class="nav-card-icon">✏️</div>
                <h2>Uzdevumi</h2>
                <p>Risini matemātikas uzdevumus un saņem MI palīdzību.</p>
            </a>

            <div class="nav-card danger" onclick="iziet()" style="cursor:pointer;">
                <div class="nav-card-icon">🚪</div>
                <h2>Iziet</h2>
                <p>Atslēgties no sava konta.</p>
            </div>

        </div>

    </main>

    <script>
        function iziet() {
            if (confirm('Vai tiešām vēlaties iziet?')) {
                location.href = 'login.php';
            }
        }
    </script>

</body>
</html>