<?php
require_once __DIR__ . '/Database.php';

class Auth
{
    public static function register(string $nom, string $email, string $motDePasse): bool
    {
        $pdo = Database::getConnection();

        // Vérifier que l'email n'existe pas déjà
        $requete = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);
        if ($requete->fetch()) {
            return false; // email déjà pris
        }

        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

        $requete = $pdo->prepare(
            "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)"
        );
        return $requete->execute([$nom, $email, $hash]);
    }

    public static function login(string $email, string $motDePasse): bool
    {
        $pdo = Database::getConnection();

        $requete = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);
        $utilisateur = $requete->fetch();

        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            // Régénérer l'ID de session à chaque connexion : empêche la
            // "fixation de session" (un attaquant qui imposerait un ID connu).
            session_regenerate_id(true);

            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['nom']     = $utilisateur['nom'];
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function estConnecte(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function exigerConnexion(): void
    {
        if (!self::estConnecte()) {
            header('Location: login.php');
            exit;
        }
    }
}

?>