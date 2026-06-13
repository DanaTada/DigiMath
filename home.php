<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sākums — DigiMath</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="app-page">

    <header class="site-header">
        <div class="site-logo">Digi<span>Math</span></div>
        <nav>
            <a href="profile.php">Mans profils</a>
        </nav>
    </header>

    <main class="app-main">

        <div class="page-heading">
            <h1>Sveiks, <span style="color:var(--blue);"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'skolēn' ?></span>! 👋</h1>
            <p>Ko šodien mācīsimies?</p>
        </div>

        <div class="home-grid">

            <a href="profile.php" class="nav-card">
                <div class="nav-card-icon">👤</div>
                <h2>Mans profils</h2>
                <p>Sava konta informācija un personas dati.</p>
            </a>

            <a href="#" class="nav-card">
                <div class="nav-card-icon">📈</div>
                <h2>Mans progress</h2>
                <p>Apskata, cik uzdevumi pabeigti un kas vēl jāapgūst.</p>
            </a>

            <a href="taskdesk.php" class="nav-card">
                <div class="nav-card-icon">✏️</div>
                <h2>Uzdevumi</h2>
                <p>Risini matemātikas uzdevumus un saņem MI palīdzību.</p>
            </a>

            <div class="nav-card danger" onclick="iziet()" style="cursor:pointer;">
                <div class="nav-card-icon">🚪</div>
                <h2>Iziet</h2>
                <p>Atslēgties no sava konta.</p>
            </div>

        </div>

    </main>

    <script>
        function iziet() {
            if (confirm('Vai tiešām vēlaties iziet?')) {
                location.href = 'login.php';
            }
        }
    </script>

</body>
</html>