<?php 
include "connect_to_db.php";
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    $check_stmt = $conn->prepare("SELECT * FROM users 
        INNER JOIN user_info ON users.user_ID = user_info.user_ID 
        WHERE users.username = ? AND users.password = ?");
    
    $check_stmt->execute([$username_input, $password_input]);
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $user = $check_result->fetch_assoc();
        
        $_SESSION['user_id']    = $user['user_ID'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['uzvards']    = $user['actual_surname'];
        $_SESSION['klase']      = $user['grade'];
        $_SESSION['skola_name'] = $user['School_name'];
        $_SESSION['e-pasts']    = $user['email'];
        
        header("Location: home.php");
        exit();
    } else {
        $error = "Nepareizs lietotājvārds vai parole. Lūdzu, mēģini vēlreiz.";
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ieiet — DigiMath</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <header class="site-header">
        <a href="landing_page.php" class="site-logo">Digi<span>Math</span></a>
        <nav>
            <a href="register.php">Reģistrēties</a>
        </nav>
    </header>

    <main class="auth-body">
        <div class="auth-card">

            <div class="auth-card-logo">
                <div class="brand">Digi<span>Math</span></div>
            </div>

            <h1>Pieslēgties</h1>
            <p class="auth-subtitle">Ienāc savā kontā un turpini mācīties</p>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="?" method="post">

                <div class="form-group">
                    <label for="username">Lietotājvārds</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        maxlength="30"
                        pattern="[A-Za-z0-9_]{1,30}"
                        placeholder="tavs_lietotājvārds"
                        required
                        autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Parole</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        maxlength="30"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Ieiet</button>

            </form>

            <p class="auth-link">Nav sava profila? <a href="register.php">Reģistrējies šeit</a></p>

        </div>
    </main>

</body>
</html>