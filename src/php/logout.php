<?php
// Démarrer la session
session_start();

// Effacer toutes les variables de session
$_SESSION = array();

// Si vous souhaitez détruire complètement la session, effacez également
// le cookie de session.
// Note : cela détruira la session, et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Enfin, détruire la session.
session_destroy();

// Rediriger l'utilisateur vers la page d'accueil
header("Location:http://localhost/php/social-media-project/index.html");
exit;
