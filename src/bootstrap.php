<?php
// Point de départ commun à toutes les pages.
require_once __DIR__ . '/../config/database.php';

// Réglage des cookies de session AVANT de démarrer celle-ci
session_set_cookie_params([
    'httponly' => true,              
    'samesite' => 'Lax',             
    'secure'   => EST_PRODUCTION,    
]);

// Démarrer la session
session_start();

// Jeton anti-CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vérifie le jeton reçu dans un formulaire POST
function verifierCsrf(): void
{
    $recu = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $recu)) {
        http_response_code(403);
        exit('Requête invalide.');
    }
}

// Envoie une réponse JSON puis arrête le script (utilisé par les fichiers d'api/)
function repondre(int $code, array $contenu): void
{
    http_response_code($code);
    echo json_encode($contenu);
    exit;
}

?>