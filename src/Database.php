<?php
require_once __DIR__ . '/../config/database.php';

class Database
{
    /*la propriete appartient a la classe et non a un objet, 
    est donc partagée entre les methodes de la classe
    */
    private static ?PDO $instance = null;

    // Singleton : on ne veut qu'UNE seule connexion PDO partagée
    // dans toute l'application, pas une nouvelle à chaque appel.
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}

?>