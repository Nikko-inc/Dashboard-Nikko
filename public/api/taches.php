<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Projet.php';
require_once __DIR__ . '/../../src/Tache.php';

header('Content-Type: application/json; charset=utf-8');

// Vérifie qu'une chaîne est une vraie date au format AAAA-MM-JJ
function dateValide(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Authetification 
if (!Auth::estConnecte()) {
    repondre(401, ['erreur' => 'Non authentifié']);
}
$userId  = (int) $_SESSION['user_id'];
$methode = $_SERVER['REQUEST_METHOD'];

// Sauvegarde des données envoyées par méthodes POST/PATCH/DELETE
$donnees = [];
if ($methode !== 'GET') {
    $donnees = json_decode(file_get_contents('php://input'), true);
    if (!is_array($donnees)) {
        repondre(400, ['erreur' => 'Corps JSON invalide']);
    }
}

// Sur quel projet veut-on agir ?
if ($methode === 'GET') {
    $projetId = (int) ($_GET['projet_id'] ?? 0);
} elseif ($methode === 'POST') {
    $projetId = (int) ($donnees['projet_id'] ?? 0);
} else {
    // Pour PATCH et DELETE : on ne reçoit que l'id de la tâche.
    // On demande à la base à quel projet elle appartient .
    $projetId = Tache::projetIdDe((int) ($donnees['id'] ?? 0)) ?? 0;
}

// Droit d'action de membre
// (Une tâche inexistante donne un projet 0 → refus, sans révéler si elle existe.)
$role = Projet::estMembre($projetId, $userId);
if (!$role) {
    repondre(403, ['erreur' => 'Accès refusé à ce projet']);
}

// Exécuter l'action.
// repondre() arrête le script, donc pas besoin de "break" dans les cas.
switch ($methode) {

    case 'GET':
        repondre(200, Tache::pourProjet($projetId));

    case 'POST':
        $description = trim($donnees['description'] ?? null);
        $titre = trim($donnees['titre'] ?? '');
        if ($titre === '' || mb_strlen($titre) > 200) {
            repondre(422, ['erreur' => 'Titre invalide']);
        }
        $echeance = $donnees['echeance'] ?? null;
        if ($echeance === '') {
            $echeance = null;
        }
        if ($echeance !== null && (!is_string($echeance) || !dateValide($echeance))) {
            repondre(422, ['erreur' => 'Date invalide']);
        }
        $id = Tache::creer($projetId, $titre, $description, $echeance);
        repondre(201, ['succes' => true, 'id' => $id]);

    case 'PATCH':
        $champs = [];

        if (isset($donnees['statut'])) {
            if (!in_array($donnees['statut'], Tache::STATUTS, true)) {
                repondre(422, ['erreur' => 'Statut invalide']);
            }
            $champs['statut'] = $donnees['statut'];
        }

        if (array_key_exists('echeance', $donnees)) {
            $echeance = $donnees['echeance'];
            if ($echeance === '') {
                $echeance = null;
            }
            if ($echeance !== null && (!is_string($echeance) || !dateValide($echeance))) {
                repondre(422, ['erreur' => 'Date invalide']);
            }
            $champs['echeance'] = $echeance;
        }

        if (array_key_exists('assigne_a', $donnees)) {
            $cible = $donnees['assigne_a'];
            if ($cible !== null) {
                $cible = (int) $cible;
                // On ne peut assigner une tâche qu'à quelqu'un qui est membre du projet
                if (!Projet::estMembre($projetId, $cible)) {
                    repondre(422, ['erreur' => "Cet utilisateur n'est pas membre du projet"]);
                }
            }
            $champs['assigne_a'] = $cible;
        }

        if (!$champs) {
            repondre(422, ['erreur' => 'Rien à modifier']);
        }
        $ok = Tache::modifier((int) $donnees['id'], $champs);
        repondre(200, ['succes' => $ok]);

    case 'DELETE':
        // Règle métier : seul le propriétaire supprime. À toi de la changer si tu veux.
        if ($role !== 'proprietaire') {
            repondre(403, ['erreur' => 'Seul le propriétaire peut supprimer']);
        }
        $ok = Tache::supprimer((int) $donnees['id']);
        repondre(200, ['succes' => $ok]);

    default:
        repondre(405, ['erreur' => 'Méthode non supportée']);
}
?>