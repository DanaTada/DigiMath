<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Ielogoties</title>
</head>
<body>

    <h1>Pieslēgties</h1>

    <form action="login.php" method="post">

        <label for="lietotajvards">Lietotājvārds:</label>
        <input type="text" id="lietotajvards" name="lietotajvards"
               maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required>

        <label for="parole">Parole:</label>
        <input type="password" id="parole" name="parole"
               maxlength="30" required>

        <button type="submit">Ielogoties</button>

    </form>

    <p>Nav sava profila? <a href="register.php">Reģistrējies šeit</a></p>

</body>
</html>
