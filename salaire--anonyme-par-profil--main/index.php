<?php
require __DIR__ . '/config.php';

$success = isset($_GET['success']) && $_GET['success'] === '1';
$error = isset($_GET['error']) ? (string) $_GET['error'] : '';

$jobTitles = [
    'Développeur informatique', 'Comptable', 'Enseignant', 'Infirmier',
    'Commercial', 'Assistant administratif', 'Technicien', 'Autre'
];
$experiences = ['0-1 an', '2-4 ans', '5-9 ans', '10 ans ou plus'];
$sectors = ['Public', 'Privé', 'Association/ONG', 'Indépendant', 'Autre'];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salaire Anonyme — comparer les salaires</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-icon">↗</span> Salaire Anonyme</a>
  <nav><a href="#declarer">Déclarer un salaire</a><a href="comparer.php">Comparer</a><a href="dashboard.php">Statistiques</a><a href="ma-declaration.php">Ma déclaration</a></nav>
</header>

<main>
  <section class="hero">
    <div class="hero-copy">
      <p class="eyebrow">TRANSPARENCE • CONFIDENTIALITÉ</p>
      <h1>Comprenez votre salaire.<br><span>En toute discrétion.</span></h1>
      <p>Partagez votre rémunération sans publier votre nom et comparez-la aux statistiques de profils similaires.</p>
      <div class="hero-actions">
        <a class="button primary" href="#declarer">Déclarer mon salaire</a>
        <a class="button secondary" href="dashboard.php">Voir les statistiques</a>
      </div>
      <p class="privacy-note">🔒 Aucun nom, téléphone ou e-mail n'est demandé.</p>
    </div>
    <div class="hero-card">
      <div class="card-top"><span>APERÇU DES PROFILS</span><span class="dot"></span></div>
      <div class="mini-stat"><div><small>Profil</small><strong>Développeur informatique</strong></div><span class="tag">2–4 ans</span></div>
      <div class="bar-row"><span>Rémunérations déclarées</span><span class="bar-value">Agrégées</span></div>
      <div class="bar"><span style="width:72%"></span></div>
      <div class="bar-row"><span>Identité publiée</span><span class="bar-value">Non</span></div>
      <div class="bar"><span class="safe-bar" style="width:100%"></span></div>
      <p class="small-muted">Exemple illustratif — aucune donnée réelle affichée.</p>
    </div>
  </section>

  <?php if ($success): ?>
    <div class="notice success">Merci ! Votre déclaration a été enregistrée sans nom ni coordonnées.</div>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <section class="section" id="declarer">
    <div class="section-heading">
      <p class="eyebrow">PARTICIPER</p>
      <h2>Déclarer un salaire</h2>
      <p>Renseignez uniquement les informations nécessaires aux comparaisons.</p>
    </div>
    <form class="salary-form" action="submit.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
      <div class="form-grid">
        <label>Métier
          <select name="job_title" required>
            <option value="">Choisir un métier</option>
            <?php foreach ($jobTitles as $job): ?>
              <option value="<?= htmlspecialchars($job, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($job, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Expérience professionnelle
          <select name="experience_level" required>
            <option value="">Choisir une tranche</option>
            <?php foreach ($experiences as $experience): ?>
              <option value="<?= htmlspecialchars($experience, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($experience, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Secteur d'activité
          <select name="sector" required>
            <option value="">Choisir un secteur</option>
            <?php foreach ($sectors as $sector): ?>
              <option value="<?= htmlspecialchars($sector, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($sector, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Salaire mensuel net (FCFA)
          <input type="number" name="salary_amount" min="1" max="1000000000" step="1" placeholder="Ex. : 250000" required>
        </label>
      </div>
      <div class="form-footer">
        <p>Votre déclaration sera utilisée dans des statistiques. Un jeton secret vous sera remis après l'envoi pour pouvoir la modifier ou la supprimer. Les groupes de moins de 5 déclarations ne sont pas affichés.</p>
        <button class="button primary" type="submit">Envoyer anonymement <span aria-hidden="true">→</span></button>
      </div>
    </form>
  </section>

  <section class="bottom-cta">
    <div><p class="eyebrow">DONNÉES COLLECTIVES</p><h2>La transparence commence ensemble.</h2><p>Plus il y a de déclarations comparables, plus les statistiques deviennent utiles.</p></div>
    <a class="button secondary" href="dashboard.php">Consulter les statistiques</a>
  </section>
</main>
<footer><span>© <?= date('Y') ?> Salaire Anonyme</span><span>Projet pédagogique • Respect de la confidentialité</span></footer>
<script src="script.js"></script>
</body>
</html>
