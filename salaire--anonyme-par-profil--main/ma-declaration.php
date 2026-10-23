<?php
require __DIR__ . '/config.php';

$jobTitles = [
    'Développeur informatique', 'Comptable', 'Enseignant', 'Infirmier',
    'Commercial', 'Assistant administratif', 'Technicien', 'Autre'
];
$experiences = ['0-1 an', '2-4 ans', '5-9 ans', '10 ans ou plus'];
$sectors = ['Public', 'Privé', 'Association/ONG', 'Indépendant', 'Autre'];

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

$row = null;     // la déclaration retrouvée grâce au jeton
$token = '';     // le jeton nettoyé (32 caractères hexadécimaux)
$notice = null;  // [type, message]

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals((string) $_SESSION['csrf_token'], $csrf)) {
        $notice = ['error', 'La session a expiré. Rechargez la page puis réessayez.'];
    } else {
        $action = (string) ($_POST['action'] ?? 'find');
        // On accepte les tirets et les espaces : on garde seulement les caractères a-f et 0-9.
        $token = preg_replace('/[^a-f0-9]/', '', strtolower((string) ($_POST['token'] ?? ''))) ?? '';

        if (strlen($token) !== 32) {
            $notice = ['error', 'Jeton invalide : il contient 32 caractères (chiffres et lettres de a à f).'];
            $token = '';
        } else {
            $hash = hash('sha256', $token);
            $find = $pdo->prepare(
                'SELECT job_title, experience_level, sector, salary_amount
                 FROM salary_submissions WHERE token_hash = :hash'
            );
            $find->execute([':hash' => $hash]);
            $row = $find->fetch() ?: null;

            if ($row === null) {
                $notice = ['error', 'Aucune déclaration ne correspond à ce jeton.'];
                $token = '';
            } elseif ($action === 'delete') {
                $pdo->prepare('DELETE FROM salary_submissions WHERE token_hash = :hash')
                    ->execute([':hash' => $hash]);
                $row = null;
                $token = '';
                $notice = ['success', 'Votre déclaration a été supprimée définitivement.'];
            } elseif ($action === 'update') {
                $job = trim((string) ($_POST['job_title'] ?? ''));
                $experience = (string) ($_POST['experience_level'] ?? '');
                $sector = (string) ($_POST['sector'] ?? '');
                $amount = filter_var($_POST['salary_amount'] ?? null, FILTER_VALIDATE_FLOAT);

                if (!in_array($job, $jobTitles, true)
                    || !in_array($experience, $experiences, true)
                    || !in_array($sector, $sectors, true)
                    || $amount === false || $amount === null || $amount <= 0 || $amount > 1000000000) {
                    $notice = ['error', 'Certaines informations sont invalides. Vérifiez le formulaire.'];
                } else {
                    $pdo->prepare(
                        'UPDATE salary_submissions
                         SET job_title = :job, experience_level = :experience,
                             sector = :sector, salary_amount = :amount
                         WHERE token_hash = :hash'
                    )->execute([
                        ':job' => $job,
                        ':experience' => $experience,
                        ':sector' => $sector,
                        ':amount' => (int) round($amount),
                        ':hash' => $hash,
                    ]);
                    $row = [
                        'job_title' => $job,
                        'experience_level' => $experience,
                        'sector' => $sector,
                        'salary_amount' => (int) round($amount),
                    ];
                    $notice = ['success', 'Votre déclaration a été mise à jour.'];
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ma déclaration — Salaire Anonyme</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-icon">↗</span> Salaire Anonyme</a>
  <nav><a href="index.php#declarer">Déclarer un salaire</a><a href="comparer.php">Comparer</a><a href="dashboard.php">Statistiques</a><a href="ma-declaration.php" aria-current="page">Ma déclaration</a></nav>
</header>
<main class="dashboard-main">
  <section class="dashboard-heading">
    <p class="eyebrow">MA DÉCLARATION</p>
    <h1>Modifier ou supprimer</h1>
    <p>Saisissez le jeton secret reçu après l'envoi de votre déclaration. Sans compte, c'est lui qui prouve qu'elle est bien la vôtre.</p>
  </section>

  <?php if ($notice !== null): ?>
    <div class="notice <?= h($notice[0]) ?>"><?= h($notice[1]) ?></div>
  <?php endif; ?>

  <?php if ($row === null): ?>
    <form class="salary-form" action="ma-declaration.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= h((string) $_SESSION['csrf_token']) ?>">
      <input type="hidden" name="action" value="find">
      <div class="form-grid single">
        <label>Jeton secret
          <input type="text" name="token" placeholder="Ex. : a1b2-c3d4-e5f6-…" autocomplete="off" required>
        </label>
      </div>
      <div class="form-footer">
        <p>Le jeton n'est jamais enregistré tel quel sur le serveur : seule son empreinte l'est. Si vous l'avez perdu, il ne peut pas être retrouvé.</p>
        <button class="button primary" type="submit">Retrouver ma déclaration <span aria-hidden="true">→</span></button>
      </div>
    </form>
  <?php else: ?>
    <form class="salary-form" action="ma-declaration.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= h((string) $_SESSION['csrf_token']) ?>">
      <input type="hidden" name="token" value="<?= h($token) ?>">
      <div class="form-grid">
        <label>Métier
          <select name="job_title" required>
            <?php foreach ($jobTitles as $option): ?>
              <option value="<?= h($option) ?>"<?= $option === $row['job_title'] ? ' selected' : '' ?>><?= h($option) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Expérience professionnelle
          <select name="experience_level" required>
            <?php foreach ($experiences as $option): ?>
              <option value="<?= h($option) ?>"<?= $option === $row['experience_level'] ? ' selected' : '' ?>><?= h($option) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Secteur d'activité
          <select name="sector" required>
            <?php foreach ($sectors as $option): ?>
              <option value="<?= h($option) ?>"<?= $option === $row['sector'] ? ' selected' : '' ?>><?= h($option) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Salaire mensuel net (FCFA)
          <input type="number" name="salary_amount" min="1" max="1000000000" step="1" value="<?= (int) $row['salary_amount'] ?>" required>
        </label>
      </div>
      <div class="form-actions">
        <button class="button primary" type="submit" name="action" value="update">Enregistrer les modifications</button>
        <button class="button danger" type="submit" name="action" value="delete" onclick="return confirm('Supprimer définitivement cette déclaration ?');">Supprimer ma déclaration</button>
      </div>
    </form>
  <?php endif; ?>
</main>
<footer><span>© <?= date('Y') ?> Salaire Anonyme</span><span>Projet pédagogique • Respect de la confidentialité</span></footer>
<script src="script.js"></script>
</body>
</html>
