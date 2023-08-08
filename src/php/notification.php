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

// Obtenir les informations du profil
$sql = "SELECT * FROM profils WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$profil = $stmt->fetch();

// Récupération des informations du profil
$sql = "SELECT id, user_id, profile_picture, bio, birthdate, created_at, updated_at FROM profils WHERE 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$profil = $stmt->fetch();

// Récupérer les notifications pour l'utilisateur actuel
$sql = "SELECT * FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$newNotifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre de notifications</title>
    <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f2f5;
    }

    .burger-menu {
      width: 80%;
      max-width: 300px;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      padding: 20px;
      background-color: #fff;
      overflow-y: auto;
      transition: transform 0.3s ease-in-out;
      box-shadow: 2px 0 5px 0 rgba(0, 0, 0, 0.1);
      transform: translateX(-100%);
    }

    .burger-menu.open {
      transform: translateX(0);
    }

    .burger-menu h2 {
      font-size: 1.25rem;
      margin-bottom: 1rem;
      color: #4b4f56;
    }

    .burger-menu a {
      color: #385898;
      text-decoration: none;
      display: block;
      margin-bottom: 0.5rem;
      font-size: 1rem;
    }

    .burger-menu .notification {
      padding: 1rem;
      margin-bottom: 1rem;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
    }

    .burger-menu .notification.unread {
      background-color: #edf2fa;
    }

    .burger-menu .notification p {
      margin: 0;
      font-size: 0.875rem;
      line-height: 1.5;
      color: #4b4f56;
    }

    .burger-menu .notification p:last-child {
      color: #8997a5;
    }

    .burger-menu .notification form {
      margin-top: 0.5rem;
    }

    .burger-menu .notification input[type="submit"] {
      background-color: #f0f2f5;
      border: none;
      border-radius: 5px;
      padding: 0.25rem 0.5rem;
      font-size: 0.75rem;
      cursor: pointer;
    }

    /* Responsive Styles */
    @media screen and (max-width: 768px) {
      .burger-menu {
        width: 100%;
        padding: 10px;
      }
    }
  </style>
</head>
<div class="notif">
        <div class="burger-menu" id="burgerMenu">
            <h2>Notifications</h2>
            <?php if (!empty($notifications)) : ?>
                <?php foreach ($notifications as $notification) : ?>
                    <div class="notification <?= $notification['status'] == 0 ? 'unread' : 'read' ?>">
                        <p><?= htmlspecialchars($notification['content']) ?></p>
                        <p><?= date("d-m-Y H:i", strtotime($notification['created_at'])) ?></p>
                        <!-- Ajout du formulaire et du bouton de suppression de notification -->
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                            <input type="hidden" name="delete_notification_id" value="<?= $notification['id'] ?>">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucune notification</p>
            <?php endif; ?>
            <a href="profil.php">Retour</a>
        </div>
</html>