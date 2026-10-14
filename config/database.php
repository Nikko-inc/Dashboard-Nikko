<?php
$hote = $_SERVER['SERVER_NAME'] ?? 'localhost';
define('EST_PRODUCTION', !in_array($hote, ['localhost', '127.0.0.1', 'dashboard.local'], true));
$EST_PRODUCTION = ($_SERVER['SERVER_NAME'] ?? '') !== 'localhost';

if ($EST_PRODUCTION) {
    define('DB_HOST', 'nom_hote_fourni_par_hebergeur');
    define('DB_NAME', 'nom_base_prod');
    define('DB_USER', 'utilisateur_prod');
    define('DB_PASS', 'mot_de_passe_prod');
    ini_set('display_errors', '0');  // ne jamais montrer les erreurs PHP aux visiteurs
    error_reporting(0);
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'dashboard');
    define('DB_USER', 'root');
    define('DB_PASS', 'gabykit08@');
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

define('DB_CHARSET', 'utf8mb4');

ini_set('display_errors', '0');   // ne rien afficher aux visiteurs
ini_set('log_errors', '1');       // enregistrer les erreurs dans un fichier de log
?>