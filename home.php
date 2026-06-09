<?php
session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Sākums</title>
</head>
<body>

    <h1>Sākums</h1>
    <p>Lietotājvārds: <?=$_SESSION['username']?></p>

    <button type="button" onclick="location.href='profile.php'">Mans profils</button>
    <button type="button">Mans progress</button>
    <button type="button" onclick="location.href='taskdesk.php'">Uzdevumi</button>
    <button type="button" onclick="iziet()">Iziet</button>

    <script>
        function iziet() {
            var atbilde = confirm('Vai tiešām vēlaties iziet?');
            if (atbilde === true) {
                location.href = 'login.php';
            }
            
        }
    </script>

</body>
</html>
