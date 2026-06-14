<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DigiMath — Matemātikas platforma</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/landing.css">
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a href="landing_page.php" class="logo logo--large">
    <span class="logo-badge">D</span>
    DigiMath
  </a>
  <div class="nav-btns">
    <a href="login.php" class="btn btn--ghost btn--sm">Pieslēgties</a>
    <a href="register.php" class="btn btn--yellow btn--sm">Izveidot kontu</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="floats" id="floats"></div>

  <div class="hero-eyebrow">✦ Matemātikas platforma skolēniem</div>

  <h1>Mācies matemātiku<br>ar <span>AI palīgu</span></h1>

  <p>DigiMath palīdz skolēniem no 1. līdz 9. klasei risināt uzdevumus,
  saprast kļūdas un nostiprināt zināšanas — ar personīga skolotāja atbalstu katrā solī.</p>

  <div class="hero-btns">
    <a href="register.php" class="btn btn--yellow btn--lg">Izveidot kontu</a>
    <a href="login.php"    class="btn btn--outline btn--lg">Jau ir konts</a>
  </div>

  <div class="hero-stats">
    <div class="hero-stat"><span class="n">9</span><span class="l">klases</span></div>
    <div class="hero-stat"><span class="n">AI</span><span class="l">palīgs</span></div>
    <div class="hero-stat"><span class="n">24/7</span><span class="l">pieejams</span></div>
    <div class="hero-stat"><span class="n">LV</span><span class="l">valodā</span></div>
  </div>
</section>

<!-- FEATURES -->
<section class="section">
  <div class="section-label">Kāpēc DigiMath?</div>
  <h2 class="section-title">Viss, kas vajadzīgs,<br><span>vienā vietā</span></h2>
  <p class="section-sub">Nevis tikai uzdevumi — pilns mācību cikls ar tūlītēju atgriezenisko saiti.</p>

  <div class="features-wrap">
    <div class="feat-card">
      <div class="feat-icon">🤖</div>
      <h3>AI paskaidrojumi</h3>
      <p>Ja atbilde ir nepareiza, AI skolotājs draudzīgi izskaidro kļūdu un parāda, kā nonākt pie pareizā risinājuma.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">🔁</div>
      <h3>Uzdevumi nostiprināšanai</h3>
      <p>Pēc kļūdas AI uzģenerē jaunu, līdzīgu uzdevumu, lai skolēns varētu nostiprināt iegūtās zināšanas.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">📊</div>
      <h3>Personīgais profils</h3>
      <p>Katram skolēnam ir savs konts ar mācību progresu, kas sniedz ieskatu attīstībā laika gaitā.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">🎯</div>
      <h3>Pēc klases un tēmas</h3>
      <p>Uzdevumi pielāgoti katrai klasei no 1. līdz 9., ievērojot mācību programmas prasības.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">🌍</div>
      <h3>Latviešu valodā</h3>
      <p>Visi paskaidrojumi, uzdevumi un saskarne — latviešu valodā, ērta bērniem un vecākiem.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">⚡</div>
      <h3>Tūlītēja pārbaude</h3>
      <p>Ievadi atbildi un uzreiz uzzini rezultātu — nav jāgaida skolotājs vai nākamā stunda.</p>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section how-bg">
  <div class="section-label">Kā tas strādā?</div>
  <h2 class="section-title">Trīs soļi līdz<br><span>panākumiem</span></h2>

  <div class="steps">
    <div class="step">
      <span class="step-num">01</span>
      <span class="step-emoji">📝</span>
      <h3>Izvēlies uzdevumu</h3>
      <p>Izvēlies savu klasi un matemātikas tēmu — saskaitīšanu, atņemšanu un citas.</p>
    </div>
    <div class="step">
      <span class="step-num">02</span>
      <span class="step-emoji">✏️</span>
      <h3>Atrisini piemēru</h3>
      <p>AI uzģenerē uzdevumu tieši tavai klasei. Ievadi atbildi un nospied «Atbildēt».</p>
    </div>
    <div class="step">
      <span class="step-num">03</span>
      <span class="step-emoji">💡</span>
      <h3>Saņem paskaidrojumu</h3>
      <p>AI paskaidros risinājumu un palīdzēs nostiprināt materiālu ar jaunu uzdevumu.</p>
    </div>
    <div class="step">
      <span class="step-num">04</span>
      <span class="step-emoji">🚀</span>
      <h3>Attīstīties katru dienu</h3>
      <p>Soli pa solim uzlabo savas matemātikas prasmes — pieejams jebkurā laikā un vietā.</p>
    </div>
  </div>
</section>

<!-- GRADES -->
<section class="section">
  <div class="section-label">Klases</div>
  <h2 class="section-title">No <span>1. līdz 9. klasei</span></h2>
  <p class="section-sub">Uzdevumi un paskaidrojumi atbilst konkrētās klases mācību programmai.</p>

  <div class="grades-row">
    <?php
    $topics = [
      1 => "Saskaitīšana un atņemšana",
      2 => "Reizināšanas tabula",
      3 => "Dalīšana un daļas",
      4 => "Daudzciparu skaitļi",
      5 => "Daļskaitļi un procenti",
      6 => "Negatīvie skaitļi",
      7 => "Lineārie vienādojumi",
      8 => "Kvadrātvienādojumi",
      9 => "Progresijas un trigonometrija",
    ];
    foreach ($topics as $kl => $tema): ?>
      <div class="grade-pill">
        <span class="gn"><?= $kl ?></span>
        <?= htmlspecialchars($tema) ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <span>DigiMath</span> &copy; <?= date('Y') ?> — Matemātikas platforma skolēniem no 1. līdz 9. klasei
</footer>

<script src="floats.js"></script>

</body>
</html>