<?php
require __DIR__ . '/config.php';

function redirectWithError(string $message): never {
    header('Location: index.php?error=' . rawurlencode($message) . '#declarer');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals((string) $_SESSION['csrf_token'], $token)) {
    redirectWithError('La session a expiré. Rechargez la page puis réessayez.');
}

$jobTitles = [
    'Développeur informatique', 'Comptable', 'Enseignant', 'Infirmier',
    'Commercial', 'Assistant administratif', 'Technicien', 'Autre'
];
$experiences = ['0-1 an', '2-4 ans', '5-9 ans', '10 ans ou plus'];
$sectors = ['Public', 'Privé', 'Association/ONG', 'Indépendant', 'Autre'];

$job = trim((string) ($_POST['job_title'] ?? ''));
$experience = (string) ($_POST['experience_level'] ?? '');
$sector = (string) ($_POST['sector'] ?? '');
$amount = filter_var($_POST['salary_amount'] ?? null, FILTER_VALIDATE_FLOAT);

if (!in_array($job, $jobTitles, true)
    || !in_array($experience, $experiences, true)
    || !in_array($sector, $sectors, true)
    || $amount === false || $amount === null || $amount <= 0 || $amount > 1000000000) {
    redirectWithError('Certaines informations sont invalides. Vérifiez le formulaire.');
}

// Jeton secret : montré une seule fois à l'auteur, seule son empreinte est stockée.
$secret = bin2hex(random_bytes(16));

$stmt = $pdo->prepare(
    'INSERT INTO salary_submissions
     (job_title, experience_level, sector, salary_amount, created_at, token_hash)
     VALUES (:job, :experience, :sector, :amount, CURDATE(), :token_hash)'
);
$stmt->execute([
    ':job' => $job,
    ':experience' => $experience,
    ':sector' => $sector,
    ':amount' => (int) round($amount),
    ':token_hash' => hash('sha256', $secret),
]);

$_SESSION['new_token'] = $secret;

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
header('Location: merci.php');
exit;
