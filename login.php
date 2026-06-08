<?php 
include "connect_to_db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Simple query - compare plain text passwords
    $check_stmt = $conn->prepare("SELECT * FROM users 
        INNER JOIN user_info ON users.user_ID = user_info.user_ID 
        WHERE users.username = ? AND users.password = ?");
    
    $check_stmt->execute([$username_input, $password_input]);
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $user = $check_result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['user_ID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['uzvards'] = $user['actual_surname'];
        $_SESSION['klase'] = $user['grade'];
        $_SESSION['skola_name'] = $user['School_name'];
        $_SESSION['e-pasts'] = $user['email'];
        
        header("Location: home.php");
        exit();
    } else {
        echo "WRONG INFO";
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Ielogoties</title>
</head>
<body>

    <h1>Pieslēgties</h1>

    <form action="?" method="post" >

        <label for="username">Lietotājvārds:</label>
        <input type="text" name="username" maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required>

        <label for="password">Parole:</label>
        <input type="password" name="password" maxlength="30" required>

        <input type="submit" value="Ielogoties">

    </form>

    <p>Nav sava profila? <a href="register.php">Reģistrējies šeit</a></p>

</body>
</html>