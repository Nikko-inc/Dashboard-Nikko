<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Auth.php';

// La déconnexion se fait par un formulaire POST avec jeton,
// pour qu'un lien piégé ne puisse pas déconnecter quelqu'un.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();
    Auth::logout();
}

header('Location: login.php');
exit;
?>