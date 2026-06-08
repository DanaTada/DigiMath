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

    <h1>Mans profils</h1>
    <p>Lietotājvārds: <?=$_SESSION['username']?> </p>

    <p>Mans progress: (placeholder)</p>

    <p>Klase:   <?=$_SESSION['klase']?> </p>

    <p>Uzvards: <?=$_SESSION['uzvards']?> </p>

    <p>Skola:   <?=$_SESSION['skola_name']?> </p>

    <p>E-pasts:   <?=$_SESSION['e-pasts']?> </p>

    <a href="home.php">Home</a>
</body>
</html>
