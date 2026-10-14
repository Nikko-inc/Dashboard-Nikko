<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Projet.php';

Auth::exigerConnexion();

// Création d'un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    verifierCsrf();
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description'] ?? '');
    if ($nom !== '' && mb_strlen($nom) <= 150) {
        Projet::creer($nom, $description, (int) $_SESSION['user_id']);
    }
    header('Location: dashboard.php');   
    exit;
}

$projets = Projet::pourUtilisateur((int) $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes projets</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Bonjour <?= htmlspecialchars($_SESSION['nom']) ?></h1>

            <form method="POST" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit">Se déconnecter</button>
            </form>
        </div>
    </header>

    <main>
        <section class="form-container">
            <h2>Nouveau projet</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="text" name="nom" placeholder="Nom du projet" required maxlength="150">
                <textarea name="description" placeholder="Description"></textarea>
                <button type="submit">Créer le projet</button>
            </form>
        </section>

        <section>
            <h2>Mes projets</h2>
            <ul>
                <?php foreach ($projets as $p): ?>
                    <li>
                        <a href="projet.php?id=<?= (int) $p['id'] ?>">
                            <?= htmlspecialchars($p['nom']) ?>
                        </a>
                        (<?= htmlspecialchars($p['role']) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>