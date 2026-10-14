<?php
require_once __DIR__ . '/Database.php';

class Tache
{
    // Les seules valeurs de statut autorisées
    public const STATUTS = ['a_faire', 'en_cours', 'termine'];

    // Recuperer toutes les informations d'une taches d'un projet
    public static function pourProjet(int $projetId): array
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare("SELECT * FROM taches WHERE projet_id = ? ORDER BY created_at DESC");
        $requete->execute([$projetId]);
        return $requete->fetchAll();
    }

    // Créer une tache sur un projet
    public static function creer(int $projetId, string $titre, ?string $description, ?string $echeance): int
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare(
            "INSERT INTO taches (projet_id, titre, description, echeance) VALUES (?, ?, ?, ?)"
        );
        $requete->execute([$projetId, $titre, $description, $echeance]);
        return (int) $pdo->lastInsertId();
    }

    // Modifie une ou plusieurs colonnes d'une tâche.
    // $champs est par exemple ['statut' => 'termine'] ou ['echeance' => '2026-11-01'].
    public static function modifier(int $id, array $champs): bool
    {
        // Liste blanche : seules ces colonnes peuvent être modifiées.
        // Les NOMS de colonnes viennent de cette liste (jamais du client),
        // et les VALEURS passent par des "?" (requête préparée).
        $autorisees = ['statut', 'echeance', 'assigne_a'];

        $morceaux = [];
        $valeurs  = [];
        foreach ($champs as $colonne => $valeur) {
            if (in_array($colonne, $autorisees, true)) {
                $morceaux[] = "$colonne = ?";
                $valeurs[]  = $valeur;
            }
        }
        if (!$morceaux) {
            return false;
        }

        $valeurs[] = $id;
        $pdo = Database::getConnection();
        $requete = $pdo->prepare(
            "UPDATE taches SET " . implode(', ', $morceaux) . " WHERE id = ?"
        );
        return $requete->execute($valeurs);
    }


    // Supprimer une tache
    public static function supprimer(int $id): bool
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare("DELETE FROM taches WHERE id = ?");
        return $requete->execute([$id]);
    }

    // À quel projet appartient cette tâche ? 
    //On recupere l'id du projet
    public static function projetIdDe(int $tacheId): ?int
    {
        $pdo = Database::getConnection();
        $requete = $pdo->prepare("SELECT projet_id FROM taches WHERE id = ?");
        $requete->execute([$tacheId]);
        $ligne = $requete->fetch();
        return $ligne ? (int) $ligne['projet_id'] : null;
    }
}


?>