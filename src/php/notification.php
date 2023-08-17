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

// Récupérer les informations du profil
$sql = "SELECT * FROM profils WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$profil = $stmt->fetch();

// Récupérer les notifications pour l'utilisateur actuel
$sql = "SELECT * FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$newNotifications = $stmt->fetchAll();

// Récupérer les notifications pour l'utilisateur actuel
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Gérer la suppression d'une notification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_notification_id'])) {
  $delete_notification_id = $_POST['delete_notification_id'];

  // Supprimer la notification de la base de données
  $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$delete_notification_id, $user_id]);

  // Rediriger l'utilisateur vers la page précédente
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Centre de notifications</title>
  <style>
    /* CSS général pour les notifications */
    .notification {
      border: 1px solid #ddd;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      background-color: #f5f6f8;
      color: #4b4e4f;
      font-size: 14px;
      width: 300px;
      box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.1);
    }

    .unread {
      border-color: #007bff;
      background-color: #cce5ff;
      color: #004085;
    }

    form {
      margin-top: 10px;
    }

    .notif h2 {
      color: #4b4e4f;
      font-size: 18px;
    }

    input[type=submit] {
      background-color: royalblue;
      border: none;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      margin: 4px 2px;
      cursor: pointer;
      border-radius: 5px;
    }

    @media only screen and (max-width: 600px) {
      .notification {
        width: auto;
      }
    }
  </style>

</head>

<body>

<div class="notif">
    <div class="burger-menu" id="burgerMenu">
        <h2>Notifications</h2>
        <?php if (!empty($notifications)) : ?>
            <?php foreach ($notifications as $notification) : ?>
                <div class="notification <?= $notification['status'] == 0 ? 'unread' : 'read' ?>">
                    <p><?= htmlspecialchars($notification['content']) ?></p>
                    <p><?= date("d-m-Y H:i", strtotime($notification['created_at'])) ?></p>
                    
                    <!-- Si la notification a un lien, affichez-le -->
                    <?php if (isset($notification['link']) && !empty($notification['link'])) : ?>
                        <a href="<?= htmlspecialchars($notification['link']) ?>" target="_blank">Lire</a>
                    <?php endif; ?>
                    
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
</div>



</body>

</html>