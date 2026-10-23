<?php
require __DIR__ . '/config.php';

header('Cache-Control: no-store');

// Le jeton n'est affiché qu'une seule fois : on le retire de la session dès la lecture.
$secret = (string) ($_SESSION['new_token'] ?? '');
unset($_SESSION['new_token']);
$display = $secret !== '' ? implode('-', str_split($secret, 4)) : '';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Merci — Salaire Anonyme</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-icon">↗</span> Salaire Anonyme</a>
  <nav><a href="index.php#declarer">Déclarer un salaire</a><a href="comparer.php">Comparer</a><a href="dashboard.php">Statistiques</a><a href="ma-declaration.php">Ma déclaration</a></nav>
</header>
<main class="dashboard-main">
  <section class="token-panel">
    <?php if ($display !== ''): ?>
      <p class="eyebrow">DÉCLARATION ENREGISTRÉE</p>
      <h2>Merci ! Voici votre jeton secret</h2>
      <p>Copiez-le ou notez-le maintenant : il ne sera plus jamais affiché. Il vous permettra de modifier ou de supprimer votre déclaration, sans compte.</p>
      <div class="token-box">
        <code id="token-value"><?= htmlspecialchars($display, ENT_QUOTES, 'UTF-8') ?></code>
        <button type="button" class="button secondary small" data-copy="#token-value">Copier</button>
      </div>
      <p class="token-note">Ne le partagez avec personne : quiconque possède ce jeton peut modifier ou supprimer votre déclaration. Si vous le perdez, il ne peut pas être retrouvé.</p>
    <?php else: ?>
      <p class="eyebrow">JETON SECRET</p>
      <h2>Aucun jeton à afficher</h2>
      <p>Le jeton est affiché une seule fois, juste après l'envoi d'une déclaration. Si vous l'avez déjà noté, utilisez la page « Ma déclaration ».</p>
    <?php endif; ?>
    <div class="link-row">
      <a class="button primary" href="ma-declaration.php">Ma déclaration</a>
      <a class="button secondary" href="dashboard.php">Voir les statistiques</a>
    </div>
  </section>
</main>
<footer><span>© <?= date('Y') ?> Salaire Anonyme</span><span>Projet pédagogique • Respect de la confidentialité</span></footer>
<script src="script.js"></script>
</body>
</html>
