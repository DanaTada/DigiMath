<?php
session_start(); 


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Sākums</title>
</head>
<body>

    <h1>Sākums</h1>
    <p>Lietotājvārds:  <?=$_SESSION['username']?> </p>

    <button type="button" onclick="location.href='profile.php'">Mans profils</button>
    <button type="button">Mans progress</button>
    <button type="button" onclick="iziet()">Iziet</button>

    <h2>Izvēlies klasi</h2>
    <div>
        <button type="button">1</button>
        <button type="button">2</button>
        <button type="button">3</button>
        <button type="button">4</button>
        <button type="button">5</button>
        <button type="button">6</button>
        <button type="button">7</button>
        <button type="button">8</button>
        <button type="button">9</button>
    </div>

    <script>
        function iziet() {
            var atbilde = confirm('Vai tiešām vēlaties iziet?');
            if (atbilde === true) {
                location.href = 'login.php';
                session_destroy();
            }
            
        }
    </script>

</body>
</html>
