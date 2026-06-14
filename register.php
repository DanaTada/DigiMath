<?php 
include "connect_to_db.php";

$error   = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'];
    $check_stmt = $conn->prepare("SELECT user_ID FROM users WHERE username = ?");
    $check_stmt->execute([$username_input]);
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Lietotājvārds jau ir aizņemts! Lūdzu, izvēlieties citu.";
    } else {
        $stmt_user = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $success_user = $stmt_user->execute([
            $username_input,
            $_POST['password']
        ]);
        
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
            if (!$success_info) {
                $error = "Kļūda: nevarēja saglabāt lietotāja informāciju.";
            } else {
                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācija — DigiMath</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <header class="site-header">
        <a href="index.php" class="site-logo">Digi<span>Math</span></a>
        <nav>
            <a href="login.php">Ieiet</a>
        </nav>
    </header>

    <main class="auth-body">
        <div class="auth-card">

            <div class="auth-card-logo">
                <div class="brand">Digi<span>Math</span></div>
            </div>

            <h1>Izveidot kontu</h1>
            <p class="auth-subtitle">Pievienojies un sāc mācīties matemātiku ar MI</p>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:.75rem 1rem;color:#15803D;font-size:.875rem;font-weight:500;margin-bottom:1.25rem;">
                    ✅ Konts veiksmīgi izveidots! <a href="login.php">Ienākt</a>
                </div>
            <?php endif; ?>

            <form action="?" method="post">

                <p class="form-divider">Pieteikšanās dati</p>

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
                        autocomplete="new-password">
                </div>

                <p class="form-divider">Par tevi</p>

                <div class="form-group">
                    <label for="vards">Vārds</label>
                    <input
                        type="text"
                        id="vards"
                        name="vards"
                        maxlength="30"
                        pattern="[A-Za-z0-9_]{1,30}"
                        placeholder="Jānis"
                        required>
                </div>

                <div class="form-group">
                    <label for="uzvards">Uzvārds</label>
                    <input
                        type="text"
                        id="uzvards"
                        name="uzvards"
                        maxlength="30"
                        pattern="[A-Za-z0-9_]{1,30}"
                        placeholder="Bērziņš"
                        required>
                </div>

                <div class="form-group">
                    <label for="klase">Klase</label>
                    <div class="select-wrap">
                        <select name="klase" id="klase">
                            <option value="1">1. klase</option>
                            <option value="2">2. klase</option>
                            <option value="3">3. klase</option>
                            <option value="4">4. klase</option>
                            <option value="5">5. klase</option>
                            <option value="6">6. klase</option>
                            <option value="7">7. klase</option>
                            <option value="8">8. klase</option>
                            <option value="9">9. klase</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="skola_name">Skolas nosaukums</label>
                    <input
                        type="text"
                        id="skola_name"
                        name="skola_name"
                        maxlength="30"
                        pattern="[A-Za-z0-9_\s]{1,30}"
                        placeholder="Rīgas 1. vidusskola"
                        required>
                </div>

                <div class="form-group">
                    <label for="e-pasts">E-pasta adrese</label>
                    <input
                        type="email"
                        id="e-pasts"
                        name="e-pasts"
                        maxlength="30"
                        placeholder="vards@skola.lv"
                        required
                        autocomplete="email">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Izveidot kontu</button>

            </form>

            <p class="auth-link">Jau ir konts? <a href="login.php">Ienākt</a></p>

        </div>
    </main>

</body>
</html>