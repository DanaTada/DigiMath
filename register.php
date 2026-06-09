<?php 
include "connect_to_db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'];
    $check_stmt = $conn->prepare("SELECT user_ID FROM users WHERE username = ?");
    $check_stmt->execute([$username_input]);
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "Lietotājvārds jau ir aizņemts! Lūdzu, izvēlieties citu.";
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
                echo "Error: Could not save user info!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reģistrācija</title>
    </head>
    <body>
        <main>
             <h1>Reģistrācija </h1>
            <form action="?" method="post">
                <label for="username">Lietotājvārds:</label><br>
                <input type="text" name="username" maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required><br>
                <label for="uzvards">Uzvārds :</label><br>
                <input type="text" name="uzvards" maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required><br>
                <label for="password">Parole :</label><br>
                <input type="password" name="password" maxlength="30" required><br>
                <label for="klase">Izvēlieties klasi (1.- 9.) :</label><br>
                <select name="klase">
                    <option value="1">1. klase</option>
                    <option value="2">2. klase</option>
                    <option value="3">3. klase</option>
                    <option value="4">4. klase</option>
                    <option value="5">5. klase</option>
                    <option value="6">6. klase</option>
                    <option value="7">7. klase</option>
                    <option value="8">8. klase</option>
                    <option value="9">9. klase</option>
                </select><br>
                <label for="skola_name">Skolas nosaukums :</label><br>
                <input type="text" name="skola_name" maxlength="30" pattern="[A-Za-z0-9_\s]{1,30}" required><br>
                <label for="e-pasts">E-pasts :</label><br>
                <input type="email" name="e-pasts" maxlength="30" required><br>
                <input type="submit" value="Izveidot kontu">
            </form>
            <p>Jau ir konts?<a href="login.php">Ienākt</a></p>
        </main>
    </body>
</html>
