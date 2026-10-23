<?php
require __DIR__ . '/config.php';

// Un groupe n'est publié que s'il contient au moins ce nombre de déclarations.
const MIN_GROUP_SIZE = 5;

// Percentile avec interpolation linéaire sur un tableau trié (0 <= $p <= 1).
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

$rows = $pdo->query(
    'SELECT job_title, experience_level, sector, salary_amount
     FROM salary_submissions
     ORDER BY job_title, experience_level, sector'
)->fetchAll();

$totalDeclarations = count($rows);

// Regroupement par profil (métier + expérience + secteur)
$buckets = [];
foreach ($rows as $row) {
    $key = $row['job_title'] . '|' . $row['experience_level'] . '|' . $row['sector'];
    if (!isset($buckets[$key])) {
        $buckets[$key] = [
            'job_title' => $row['job_title'],
            'experience_level' => $row['experience_level'],
            'sector' => $row['sector'],
            'amounts' => [],
        ];
    }
    $buckets[$key]['amounts'][] = (float) $row['salary_amount'];
}

// On ne garde que les groupes assez grands, avec médiane et quartiles
$groups = [];
foreach ($buckets as $bucket) {
    $amounts = $bucket['amounts'];
    if (count($amounts) < MIN_GROUP_SIZE) {
        continue;
    }
    sort($amounts);
    $groups[] = [
        'job_title' => $bucket['job_title'],
        'experience_level' => $bucket['experience_level'],
        'sector' => $bucket['sector'],
        'sample_size' => count($amounts),
        'median_salary' => percentile($amounts, 0.5),
        'q1_salary' => percentile($amounts, 0.25),
        'q3_salary' => percentile($amounts, 0.75),
    ];
}
$visibleGroups = count($groups);
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money(float|string $amount, string $currency): string {
    return number_format((float)$amount, 0, ',', ' ') . ' ' . $currency;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Statistiques — Salaire Anonyme</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-icon">↗</span> Salaire Anonyme</a>
  <nav><a href="index.php#declarer">Déclarer un salaire</a><a href="comparer.php">Comparer</a><a href="dashboard.php" aria-current="page">Statistiques</a><a href="ma-declaration.php">Ma déclaration</a></nav>
</header>
<main class="dashboard-main">
  <section class="dashboard-heading">
    <p class="eyebrow">TABLEAU DE BORD</p>
    <h1>Statistiques salariales</h1>
    <p>Les résultats sont regroupés par métier, expérience et secteur. Les salaires sont des montants mensuels nets en FCFA.</p>
  </section>
  <section class="stats-cards">
    <div class="stat-card"><span>Déclarations reçues</span><strong><?= number_format($totalDeclarations, 0, ',', ' ') ?></strong><small>Nombre total de contributions</small></div>
    <div class="stat-card"><span>Groupes publiables</span><strong><?= number_format($visibleGroups, 0, ',', ' ') ?></strong><small>Au moins 5 déclarations par groupe</small></div>
  </section>
  <section class="table-panel">
    <div class="table-heading"><div><h2>Comparaison par profil</h2><p>Les groupes trop petits restent masqués pour limiter les risques d'identification.</p></div><a class="button primary" href="index.php#declarer">Ajouter une déclaration</a></div>
    <?php if (!$groups): ?>
      <div class="empty-state"><div class="empty-icon">▦</div><h3>Pas encore assez de données</h3><p>Un groupe apparaît après au moins 5 déclarations correspondant au même profil.</p><a href="index.php#declarer" class="text-link">Être parmi les premiers contributeurs →</a></div>
    <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Métier</th><th>Expérience</th><th>Secteur</th><th>Déclarations</th><th>Salaire médian</th><th>Fourchette courante</th></tr></thead>
        <tbody>
        <?php foreach ($groups as $group): ?>
          <tr>
            <td><?= h($group['job_title']) ?></td>
            <td><?= h($group['experience_level']) ?></td>
            <td><?= h($group['sector']) ?></td>
            <td><?= (int) $group['sample_size'] ?></td>
            <td class="salary-cell"><?= h(money($group['median_salary'], 'FCFA')) ?></td>
            <td><?= h(money($group['q1_salary'], 'FCFA')) ?> – <?= h(money($group['q3_salary'], 'FCFA')) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php endif; ?>
  </section>
  <p class="disclaimer">Ces statistiques reposent sur les déclarations des utilisateurs. Elles ne constituent pas une enquête représentative du marché du travail. La fourchette courante couvre la moitié centrale des déclarations (du 1er au 3e quartile).</p>
</main>
<footer><span>© <?= date('Y') ?> Salaire Anonyme</span><span>Projet pédagogique • Respect de la confidentialité</span></footer>
</body>
</html>
