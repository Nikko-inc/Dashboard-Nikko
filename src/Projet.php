<?php
require_once __DIR__ . '/Database.php';

class Projet
{
    public static function creer(string $nom, string $description, int $ownerId): int
    {
        $pdo = Database::getConnection();

        $requete = $pdo->prepare(
            "INSERT INTO projets (nom, description, owner_id) VALUES (?, ?, ?)"
        );
        $requete->execute([$nom, $description, $ownerId]);
        $projetId = (int) $pdo->lastInsertId();

        // Le créateur devient automatiquement membre "proprietaire"
        $requete = $pdo->prepare(
            "INSERT INTO projet_membres (projet_id, user_id, role) VALUES (?, ?, 'proprietaire')"
        );
        $requete->execute([$projetId, $ownerId]);

        return $projetId;
    }

    // Tous les projets dont l'utilisateur est membre (proprietaire OU membre)
    public static function pourUtilisateur(int $userId): array
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare(
            "SELECT p.*, pm.role
             FROM projets p
             JOIN projet_membres pm ON pm.projet_id = p.id
             WHERE pm.user_id = ?
             ORDER BY p.created_at DESC"
        );
        $requete->execute([$userId]);
        return $requete->fetchAll();
    }

    // Recuperer toutes les informations concernant un projet
    public static function trouverParId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
        $requete->execute([$id]);
        $resultat = $requete->fetch();
        return $resultat ?: null;
    }

    // Supprimer un projet
    public static function supprimer(int $id): bool
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare("DELETE FROM projets WHERE id = ?");
        return $requete->execute([$id]);
    }

    // Retourne le role d'un individu sur un projet
    public static function estMembre(int $projetId, int $userId)
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare(
            "SELECT role FROM projet_membres WHERE projet_id = ? AND user_id = ?"
        );
        $requete->execute([$projetId, $userId]);
        $ligne = $requete->fetch();
        return $ligne ? $ligne['role'] : false; // renvoie le rôle, ou false si absent
    }

    // Liste des membres d'un projet (pour le menu d'assignation)
    public static function membres(int $projetId): array
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare(
            "SELECT u.id, u.nom, pm.role
            FROM projet_membres pm
            JOIN utilisateurs u ON u.id = pm.user_id
            WHERE pm.projet_id = ?
            ORDER BY u.nom"
        );
        $requete->execute([$projetId]);
        return $requete->fetchAll();
    }

    // Ajouter un utilisateur (identifié par son email) au projet.
    // Renvoie 'ok', 'inconnu' (aucun compte avec cet email) ou 'deja' (déjà membre).
    public static function ajouterMembre(int $projetId, string $email): string
    {
        $pdo = Database::getConnection();

        $requete = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);
        $utilisateur = $requete->fetch();
        if (!$utilisateur) {
            return 'inconnu';
        }

        // INSERT IGNORE : si la ligne existe déjà, rien n'est ajouté
        $requete = $pdo->prepare(
            "INSERT IGNORE INTO projet_membres (projet_id, user_id, role) VALUES (?, ?, 'membre')"
        );
        $requete->execute([$projetId, $utilisateur['id']]);

        return $requete->rowCount() === 1 ? 'ok' : 'deja';
    }


}

?>