
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

// Obtenir les amis de l'utilisateur
$sql = "SELECT users.* FROM friends 
        JOIN users ON friends.friend_id = users.id 
        WHERE friends.user_id = ? AND friends.status = 'ACCEPTED'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$friends = $stmt->fetchAll();

// Supprimer ami: status = 'DELETED'
if (isset($_POST['delete_friend_id'])) {
    $friend_to_delete_id = $_POST['delete_friend_id'];

    $sql = "UPDATE friends 
            SET status = 'DELETED'
            WHERE user_id = ? AND friend_id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$user_id, $friend_to_delete_id]);

    if ($success) {
        echo "Status amicale mit à jour: 'DELETED'.";
    } else {
        echo "Il y a eu un problème lors de la suppression.";
    }
} else {
    echo "No delete_friend_id provided.";
}

// Block friend: status = 'BLOCKED'
if (isset($_POST['block_friend_id'])) {
    $friend_to_block_id = $_POST['block_friend_id'];

    $sql = "UPDATE friends 
            SET status = 'BLOCKED'
            WHERE user_id = ? AND friend_id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$user_id, $friend_to_block_id]);

    if ($success) {
        echo "Status amicale mit à jour: 'BLOCKED'.";
    } else {
        echo "Il y a eu un problème lors du blocage.";
    }
} else {
    echo "No block_friend_id provided.";
}
?>




<p><a href="profil.php?delete_friend=<?= $friend['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet ami(e) ?')">Supprimer l'ami</a></p>
