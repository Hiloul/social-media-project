<?php
session_start();
require 'dbconfig.php';

// Vérifier si un utilisateur est connecté
if (!isset($_SESSION['username'])) {
    // Rediriger vers la page de connexion
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur à partir du nom d'utilisateur
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

// Maintenant $user['id'] contient l'ID de l'utilisateur
$user_id = $user['id'];

// Vérification de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $setting_name = $_POST['setting_name'];
    $setting_value = $_POST['setting_value'];

    // Vérifiez si le réglage existe déjà pour l'utilisateur
    $sql = "SELECT * FROM settings WHERE user_id = ? AND setting_name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $setting_name]);
    $setting = $stmt->fetch();

    if ($setting) {
        // Le réglage existe déjà, donc mise à jour
        $sql = "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$setting_value, $setting['id']]);
    } else {
        // Le réglage n'existe pas, donc création
        $sql = "INSERT INTO settings (user_id, setting_name, setting_value, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $setting_name, $setting_value]);
    }

    // Redirigez l'utilisateur vers la page des paramètres avec un message de réussite
    $_SESSION['success_message'] = "Paramètres mis à jour avec succès!";
    header('Location: setting_account.php');
    exit();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Paramètres du compte</title>
    <style>
    .burger-menu {
        width: 260px;
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        padding: 20px;
        background-color: #3b5998;
        color: #fff;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        font-family: Arial, sans-serif;
        box-shadow: -2px 0px 5px 0px rgba(0,0,0,0.1);
    }

    .burger-menu.open {
        transform: translateX(0);
    }

    .burger-menu h2 {
        color: #fff;
        font-size: 22px;
        margin-bottom: 20px;
    }

    .burger-menu a {
        color: #fff;
        text-decoration: none;
        font-size: 16px;
        line-height: 2.5;
        display: block;
    }

    .burger-menu a:hover {
        background-color: rgba(0,0,0,0.1);
        border-radius: 5px;
        padding: 2px 10px;
    }
</style>

</head>
<body>
    <div class="burger-menu" id="burgerMenu">
        <h2>Paramètres</h2>
        <a href="edit_profil.php">Mise à jour du profil</a>
        <a href="change_password.php">Changer le mot de passe</a>
        <a href="privacy_settings.php">Paramètres de confidentialité</a>
        <a href="notification.php">Paramètres de notification</a>
        <a href="profil.php">Retour</a>
    </div>

    <button id="burgerButton">Paramètres</button>

    <script>
        var burgerMenu = document.getElementById('burgerMenu');
        var burgerButton = document.getElementById('burgerButton');

        burgerButton.addEventListener('click', function() {
            burgerMenu.classList.toggle('open');
        });
    </script>
</body>
</html>

