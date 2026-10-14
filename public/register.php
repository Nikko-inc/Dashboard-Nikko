<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Auth.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $nom   = trim($_POST['nom'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if ($nom === '' || mb_strlen($nom) > 100) {
        $erreur = "Nom invalide.";
    } elseif (!$email) {
        $erreur = "Email invalide.";
    } elseif (strlen($mdp) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!Auth::register($nom, $email, $mdp)) {
        $erreur = "Cet email est déjà utilisé.";
    } else {
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div>
        <h1>Créer un compte</h1>
        <?php if ($erreur): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>    
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="nom" placeholder="Nom" required maxlength="100">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe (8 caractères min.)" required>
            <button type="submit">S'inscrire</button>
        </form>
        <p><a href="login.php">J'ai déjà un compte</a></p>
    </div>
</body>
</html>

