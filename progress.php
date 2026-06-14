<?php
// progress.php - Core calculation functions

function getUserProgress($userId, $conn) {
    // Get user's grade
    $stmt = $conn->prepare("SELECT grade FROM user_info WHERE user_ID = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->get_result()->fetch_assoc();
    $userGrade = $userInfo['grade'];
    
    // Get completed tasks
    $stmt = $conn->prepare("SELECT uzd_completed FROM user_info WHERE user_ID = ?");
    $stmt->execute([$userId]);
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    $completedTasks = [];
    if (!empty($userData['uzd_completed'])) {
        $completedTasks = explode(',', $userData['uzd_completed']);
        $completedTasks = array_map('trim', $completedTasks);
    }
    
    // Load tasks from JSON
    $json = file_get_contents('uzdevumi.json');
    $data = json_decode($json, true);
    $allTasks = $data['tasks'];
    
    // Calculate progress for grades 1-9
    $grades = [1,2,3,4,5,6,7,8,9];
    $progressData = [];
    
    foreach ($grades as $grade) {
        // Count total tasks in this grade
        $totalTasks = 0;
        $completedCount = 0;
        
        foreach ($allTasks as $task) {
            if ($task['grade'] == $grade) {
                $totalTasks++;
                if (in_array($task['id'], $completedTasks)) {
                    $completedCount++;
                }
            }
        }
        
        // Calculate percentage based on user's grade
        if ($grade < $userGrade) {
            $percentage = 100; // Auto-complete lower grades
            $completedCount = $totalTasks; // Show all as completed
        } elseif ($grade == $userGrade) {
            $percentage = $totalTasks > 0 ? ($completedCount / $totalTasks) * 100 : 100;
        } else {
            $percentage = 0; // Locked
        }
        
        $progressData[$grade] = [
            'total' => $totalTasks,
            'completed' => $completedCount,
            'percentage' => round($percentage, 1)
        ];
    }
    
    return [
        'user_grade' => $userGrade,
        'grades' => $progressData
    ];
}
?>