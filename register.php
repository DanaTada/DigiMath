<?php
include "connect_to_db.php";

$kludas = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'];

    $check_stmt = $conn->prepare("SELECT user_ID FROM users WHERE username = ?");
    $check_stmt->execute([$username_input]);
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $kludas = 'Lietotājvārds jau ir aizņemts! Lūdzu, izvēlieties citu.';
    } else {
        $stmt_user = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $success_user = $stmt_user->execute([$username_input, $_POST['password']]);

        if ($success_user) {
            $new_user_id = $conn->insert_id;
            $sql_info = "INSERT INTO user_info (user_ID, username, actual_surname, grade, School_name, email) 
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_info = $conn->prepare($sql_info);
            $success_info = $stmt_info->execute([
                $new_user_id,
                $username_input,
                $_POST['uzvards'],
                $_POST['klase'],
                $_POST['skola_name'],
                $_POST['e-pasts']
            ]);

            if ($success_info) {
                header("Location: login.php");
                exit();
            } else {
                $kludas = 'Kļūda! Neizdevās saglabāt informāciju.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reģistrācija — DigiMath</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/register.css">
</head>
<body class="page-auth">

  <a href="landing_page.php" class="back-link">← Atpakaļ uz sākumu</a>

  <a href="landing_page.php" class="logo" style="margin-bottom:36px">
    <span class="logo-badge">D</span>
    DigiMath
  </a>

  <div class="card" style="width:100%;max-width:460px">
    <h1>Izveido kontu</h1>
    <p class="sub">Pievienojies DigiMath un sāc mācīties jau šodien.</p>

    <?php if ($kludas): ?>
      <div class="alert alert--error">⚠️ <?= htmlspecialchars($kludas) ?></div>
    <?php endif; ?>

    <form action="register.php" method="post">
      <div class="row-2">
        <div class="field">
          <label for="username">Lietotājvārds</label>
          <input type="text" id="username" name="username"
                 maxlength="30" pattern="[A-Za-z0-9_]{1,30}"
                 placeholder="lietotajvards" required>
        </div>
        <div class="field">
          <label for="uzvards">Uzvārds</label>
          <input type="text" id="uzvards" name="uzvards"
                 maxlength="30" placeholder="Bērziņš" required>
        </div>
      </div>

      <div class="field">
        <label for="password">Parole</label>
        <input type="password" id="password" name="password"
               maxlength="30" placeholder="••••••••" required>
      </div>

      <hr class="divider">

      <div class="row-2">
        <div class="field">
          <label for="klase">Klase</label>
          <div class="select-wrap">
            <select id="klase" name="klase">
              <?php for ($i = 1; $i <= 9; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?>. klase</option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="e-pasts">E-pasts</label>
          <input type="email" id="e-pasts" name="e-pasts"
                 maxlength="50" placeholder="epasts@pasts.lv" required>
        </div>
      </div>

      <div class="field">
        <label for="skola_name">Skolas nosaukums</label>
        <input type="text" id="skola_name" name="skola_name"
               maxlength="60" placeholder="Rīgas 1. vidusskola" required>
      </div>

      <button type="submit" class="btn btn--yellow btn--md btn--full" style="margin-top:6px">
        Izveidot kontu
      </button>
    </form>
  </div>

  <p class="foot-link">Jau ir konts? <a href="login.php">Pieslēgties</a></p>

</body>
</html>