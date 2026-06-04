<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Ielogoties</title>
</head>
<body>

    <h1>Pieslēgties</h1>

    <form action="login.php" method="post">

        <label for="username">Lietotājvārds:</label>
        <input type="text" name="username" maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required>

        <label for="password">Parole:</label>
        <input type="password"  name="password" maxlength="30" required>

        <input type="submit" value="Ielogoties">

    </form>

    <p>Nav sava profila? <a href="register.php">Reģistrējies šeit</a></p>

</body>
</html>
