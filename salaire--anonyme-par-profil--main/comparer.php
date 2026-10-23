<?php
require __DIR__ . '/config.php';

// Même seuil que le tableau de bord : en dessous, aucune statistique n'est affichée.
const MIN_GROUP_SIZE = 5;

$jobTitles = [
    'Développeur informatique', 'Comptable', 'Enseignant', 'Infirmier',
    'Commercial', 'Assistant administratif', 'Technicien', 'Autre'
];
$experiences = ['0-1 an', '2-4 ans', '5-9 ans', '10 ans ou plus'];
$sectors = ['Public', 'Privé', 'Association/ONG', 'Indépendant', 'Autre'];

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money(float|string $amount, string $currency): string {
    return number_format((float)$amount, 0, ',', ' ') . ' ' . $currency;
}
function percentile(array $sorted, float $p): float {
    $n = count($sorted);
    if ($n === 1) {
        return (float) $sorted[0];
    }
    $pos = ($n - 1) * $p;
    $low = (int) floor($pos);
    $high = (int) ceil($pos);
    return $sorted[$low] + ($sorted[$high] - $sorted[$low]) * ($pos - $low);
}

$job = '';
$experience = '';
$sector = '';
$amountInput = '';
$error = '';
$result = null;

if (isset($_GET['job_title'])) {
    $job = trim((string) $_GET['job_title']);
    $experience = (string) ($_GET['experience_level'] ?? '');
    $sector = (string) ($_GET['sector'] ?? '');
    $amountInput = (string) ($_GET['salary_amount'] ?? '');
    $amount = filter_var($amountInput, FILTER_VALIDATE_FLOAT);

    if (!in_array($job, $jobTitles, true)
        || !in_array($experience, $experiences, true)
        || !in_array($sector, $sectors, true)
        || $amount === false || $amount <= 0 || $amount > 1000000000) {
        $error = 'Certaines informations sont invalides. Vérifiez le formulaire.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT salary_amount FROM salary_submissions
             WHERE job_title = :job AND experience_level = :experience AND sector = :sector
             ORDER BY salary_amount'
        );
        $stmt->execute([':job' => $job, ':experience' => $experience, ':sector' => $sector]);
        $amounts = array_map('floatval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $count = count($amounts);

        $result = ['count' => $count, 'amount' => (float) $amount, 'enough' => $count >= MIN_GROUP_SIZE];
        if ($result['enough']) {
            $median = percentile($amounts, 0.5);
            $q1 = percentile($amounts, 0.25);
            $q3 = percentile($amounts, 0.75);
            // Trois positions seulement (jamais de rang exact), pour ne pas révéler de salaires individuels.
            if ($amount < $q1) {
                $position = 'En dessous de la fourchette courante';
            } elseif ($amount > $q3) {
                $position = 'Au-dessus de la fourchette courante';
            } else {
                $position = 'Dans la fourchette courante';
            }
            $gap = ($amount / $median - 1) * 100;
            $result += [
                'median' => $median,
                'q1' => $q1,
                'q3' => $q3,
                'position' => $position,
                'gap' => ($gap >= 0 ? '+' : '−') . number_format(abs($gap), 0, ',', ' ') . ' % par rapport à la médiane',
            ];
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comparer mon salaire — Salaire Anonyme</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-icon">↗</span> Salaire Anonyme</a>
  <nav><a href="index.php#declarer">Déclarer un salaire</a><a href="comparer.php" aria-current="page">Comparer</a><a href="dashboard.php">Statistiques</a><a href="ma-declaration.php">Ma déclaration</a></nav>
</header>
<main class="dashboard-main">
  <section class="dashboard-heading">
    <p class="eyebrow">COMPARAISON</p>
    <h1>Comparer mon salaire</h1>
    <p>Choisissez un profil et saisissez votre salaire mensuel net : vous voyez où vous vous situez par rapport aux déclarations du même profil.</p>
  </section>

  <?php if ($error !== ''): ?>
    <div class="notice error"><?= h($error) ?></div>
  <?php endif; ?>

  <form class="salary-form" action="comparer.php" method="get">
    <div class="form-grid">
      <label>Métier
        <select name="job_title" required>
          <option value="">Choisir un métier</option>
          <?php foreach ($jobTitles as $option): ?>
            <option value="<?= h($option) ?>"<?= $option === $job ? ' selected' : '' ?>><?= h($option) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Expérience professionnelle
        <select name="experience_level" required>
          <option value="">Choisir une tranche</option>
          <?php foreach ($experiences as $option): ?>
            <option value="<?= h($option) ?>"<?= $option === $experience ? ' selected' : '' ?>><?= h($option) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Secteur d'activité
        <select name="sector" required>
          <option value="">Choisir un secteur</option>
          <?php foreach ($sectors as $option): ?>
            <option value="<?= h($option) ?>"<?= $option === $sector ? ' selected' : '' ?>><?= h($option) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Votre salaire mensuel net (FCFA)
        <input type="number" name="salary_amount" min="1" max="1000000000" step="1" placeholder="Ex. : 250000" value="<?= h($amountInput) ?>" required>
      </label>
    </div>
    <div class="form-footer">
      <p>Cette comparaison n'enregistre rien : elle n'ajoute aucune déclaration.</p>
      <button class="button primary" type="submit">Comparer <span aria-hidden="true">→</span></button>
    </div>
  </form>

  <?php if ($result !== null && !$result['enough']): ?>
    <div class="notice info">Pas assez de déclarations pour ce profil (<?= (int) $result['count'] ?> sur <?= MIN_GROUP_SIZE ?> nécessaires). Les groupes trop petits restent masqués pour protéger l'anonymat. <a class="text-link" href="index.php#declarer">Ajouter une déclaration →</a></div>
  <?php elseif ($result !== null): ?>
    <section class="stats-cards">
      <div class="stat-card"><span>Salaire médian de ce profil</span><strong><?= h(money($result['median'], 'FCFA')) ?></strong><small>Fourchette courante : <?= h(money($result['q1'], 'FCFA')) ?> – <?= h(money($result['q3'], 'FCFA')) ?> · <?= (int) $result['count'] ?> déclarations</small></div>
      <div class="stat-card"><span>Votre salaire</span><strong><?= h(money($result['amount'], 'FCFA')) ?></strong><small><?= h($result['position']) ?> · <?= h($result['gap']) ?></small></div>
    </section>
    <p class="disclaimer">La fourchette courante couvre la moitié centrale des déclarations (du 1er au 3e quartile). Ces statistiques reposent sur des déclarations d'utilisateurs et ne constituent pas une enquête représentative.</p>
  <?php endif; ?>
</main>
<footer><span>© <?= date('Y') ?> Salaire Anonyme</span><span>Projet pédagogique • Respect de la confidentialité</span></footer>
<script src="script.js"></script>
</body>
</html>
