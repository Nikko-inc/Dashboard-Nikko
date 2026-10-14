<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Projet.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::estConnecte()) {
    repondre(401, ['erreur' => 'Non authentifié']);
}
$userId  = (int) $_SESSION['user_id'];
$methode = $_SERVER['REQUEST_METHOD'];



// GET : lister les membres (tout membre du projet peut le faire)
if ($methode === 'GET') {
    $projetId = (int) ($_GET['projet_id'] ?? 0);
    if (!Projet::estMembre($projetId, $userId)) {
        repondre(403, ['erreur' => 'Accès refusé à ce projet']);
    }
    repondre(200, Projet::membres($projetId));
}

// POST : ajouter un membre (propriétaire seulement)
if ($methode === 'POST') {
    $donnees  = json_decode(file_get_contents('php://input'), true);
    $projetId = (int) ($donnees['projet_id'] ?? 0);

    if (Projet::estMembre($projetId, $userId) !== 'proprietaire') {
        repondre(403, ['erreur' => 'Seul le propriétaire peut ajouter des membres']);
    }

    $email = filter_var(trim($donnees['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        repondre(422, ['erreur' => 'Email invalide']);
    }

    switch (Projet::ajouterMembre($projetId, $email)) {
        case 'inconnu':
            repondre(404, ['erreur' => "Aucun compte avec cet email : la personne doit d'abord s'inscrire"]);
        case 'deja':
            repondre(409, ['erreur' => 'Cette personne est déjà membre du projet']);
        default:
            repondre(201, ['succes' => true]);
    }
}

repondre(405, ['erreur' => 'Méthode non supportée']);

?>