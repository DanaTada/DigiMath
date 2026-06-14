<?php
session_start();
include "connect_to_db.php";

$kludas = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Pārbauda lietotāju un paņem arī informāciju no user_info
    $stmt = $conn->prepare("SELECT * FROM users 
        INNER JOIN user_info ON users.user_ID = user_info.user_ID 
        WHERE users.username = ? AND users.password = ?");
    $stmt->execute([$username_input, $password_input]);
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id']    = $user['user_ID'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['uzvards']    = $user['actual_surname'];
        $_SESSION['klase']      = $user['grade'];
        $_SESSION['skola_name'] = $user['School_name'];
        $_SESSION['e-pasts']    = $user['email'];

        header("Location: home.php");
        exit();
    } else {
        $kludas = 'Nepareizs lietotājvārds vai parole.';
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pieslēgties — DigiMath</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body class="page-auth">

  <a href="index.php" class="back-link">← Atpakaļ uz sākumu</a>

  <a href="index.php" class="logo" style="margin-bottom:36px">
    <span class="logo-badge">D</span>
    DigiMath
  </a>

  <div class="card" style="width:100%;max-width:420px">
    <h1>Laipni lūgti atpakaļ!</h1>
    <p class="sub">Ievadi savus datus, lai turpinātu.</p>

    <?php if ($kludas): ?>
      <div class="alert alert--error">⚠️ <?= htmlspecialchars($kludas) ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
      <div class="field">
        <label for="username">Lietotājvārds</label>
        <input type="text" id="username" name="username"
               maxlength="30" pattern="[A-Za-z0-9_]{1,30}"
               placeholder="tavs_lietotajvards" required>
      </div>
      <div class="field">
        <label for="password">Parole</label>
        <input type="password" id="password" name="password"
               maxlength="30" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn--yellow btn--md btn--full" style="margin-top:6px">
        Pieslēgties
      </button>
    </form>
  </div>

  <p class="foot-link">Nav konta? <a href="register.php">Reģistrējies šeit</a></p>

</body>
</html>