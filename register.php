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
            <form  action="?"> <!-- action="?"  input apstrade notiek ši paša failā -->

                <label for="username">Lietotājvārds:</label><br>
                <input type="text" id="fname" name="username" maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required><br>

                <label for="password">Parole :</label><br>
                <input type="text" id="fname" name="password" maxlength="30" pattern="[A-Za-z0-9_]{1,30}" required><br>

                <label for="klase">Izvēlieties klasi (1.- 9.) :</label><br>
                <select id="cars" name="klase">
                <option value="kl_1">1.klase</option>
                <option value="kl_2">2.klase</option>
                <option value="kl_3">3.klase</option>
                <option value="kl_4">4.klase</option>
                <option value="kl_5">5.klase</option>
                <option value="kl_6">6.klase</option>
                <option value="kl_7">7.klase</option>
                <option value="kl_8">8.klase</option>
                <option value="kl_9">9.klase</option>
                </select><br>

                <input type="submit" value="Izveidot kontu">

            </form>
            <p>Jau ir konts?<a href="login.php">Ienākt</a></p>

             
        </main>
    </body>
</html>
