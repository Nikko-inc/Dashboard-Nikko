<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Auth.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $erreur = "Email invalide.";
    }
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if (Auth::login($email, $motDePasse)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="form-container">
        <h1>Connexion</h1>
        <?php if ($erreur): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <form method="POST">
            <!-- csrf token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <p><a href="register.php">Créer un compte</a></p>
    </div>
</body>
</html>