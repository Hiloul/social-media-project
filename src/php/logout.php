<?php
// Connexion BDD 
require 'dbconfig.php';

// Démarrer la session
session_start();

// Si l'utilisateur est connecté, mettez à jour son statut de connexion
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Mettre à jour le statut de connexion dans la base de données
    $sqlUpdate = "UPDATE users SET is_connected = 0 WHERE username = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$username]);
}

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
?>
