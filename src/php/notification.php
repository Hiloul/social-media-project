<?php
session_start();
require 'dbconfig.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
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
    <title>Document</title>
    <style>
    .burger-menu {
    width: 300px;
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
    z-index: 999;
}

.burger-menu h2 {
    color: #fff;
    font-size: 24px;
    margin-bottom: 20px;
}

.burger-menu a {
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    margin-top: 20px;
    display: block;
}

.burger-menu .notification {
    background-color: #4a69bd;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.burger-menu .notification.unread {
    background-color: #6a89cc;
}

.burger-menu .notification p {
    margin: 0;
    font-size: 16px;
    line-height: 1.5;
}

.burger-menu-btn {
    position: fixed;
    right: 20px;
    top: 20px;
    z-index: 1000;
}
</style>
</head>
<body>
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
        <a href="profil.php">Retour au profil</a>
    </div>
    <button class="burger-menu-btn" id="burgerMenuBtn">Menu</button>

    <script>
        document.getElementById('burgerMenuBtn').addEventListener('click', function () {
            document.getElementById('burgerMenu').style.transform = 'translateX(0)';
        });
    </script>
</body>
</html>



