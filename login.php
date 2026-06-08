<?php include "connect_to_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    $check_stmt = $conn->prepare("SELECT user_ID FROM users WHERE username = ? AND password = ?");
    $check_stmt->execute([$username_input, $password_input]);
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0){
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
        <input type="password"  name="password" maxlength="30" required>

        <input type="submit" value="Ielogoties">

    </form>

    <p>Nav sava profila? <a href="register.php">Reģistrējies šeit</a></p>

</body>
</html>
