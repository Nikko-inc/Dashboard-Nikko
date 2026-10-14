<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Projet.php';

Auth::exigerConnexion();

$projetId = (int) ($_GET['id'] ?? 0);
$projet   = Projet::trouverParId($projetId);
$role     = Projet::estMembre($projetId, (int) $_SESSION['user_id']);

// Projet inexistant OU je n'en suis pas membre : même réponse dans les deux cas
if (!$projet || !$role) {
    http_response_code(404);
    exit('Projet introuvable.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($projet['nom']) ?></title>
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-projet-id="<?= $projetId ?>" data-role="<?= htmlspecialchars($role) ?>">

    <p><a href="dashboard.php">← Mes projets</a></p>
    <h1><?= htmlspecialchars($projet['nom']) ?></h1>
    <p><?= nl2br(htmlspecialchars($projet['description'] ?? '')) ?></p>

    <div class="barre-progression-conteneur">
        <div id="barre-progression">0%</div>
    </div>

    <section>
        <h2>Équipe</h2>
        <ul id="liste-membres"></ul>

        <?php if ($role === 'proprietaire'): ?>
            <form id="form-nouveau-membre">
                <input type="email" id="email-membre" placeholder="Email d'un collègue inscrit" required>
                <button type="submit">Ajouter à l'équipe</button>
            </form>
        <?php endif; ?>
    </section>

    <form id="form-nouvelle-tache">
        <input type="text" id="titre-tache" placeholder="Nouvelle tâche" required maxlength="200">
        <input type="text" id="description" placeholder="Une description de la tache" maxlength="250">
        <input type="date" id="echeance-tache">
        <button type="submit">Ajouter</button>
    </form>

    <ul id="liste-taches"></ul>

    <script src="assets/js/app.js"></script>
</body>
</html>