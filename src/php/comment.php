<?php
session_start();
require 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['post_id'], $_POST['content'], $_SESSION['username'])) {
        $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $username = $_SESSION['username'];

        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $user_id = $user['id'];

        $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$post_id, $user_id, $content])) {
            echo "Commentaire ajouté avec succès!";
            
            // Récupérez l'ID du propriétaire du post
            $sql = "SELECT user_id FROM posts WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$post_id]);
            $post_owner = $stmt->fetch();
            $post_owner_id = $post_owner['user_id'];

            // Créez une notification pour le propriétaire du post
            $notification_content = $username . " a commenté votre post.";
            $link_to_post = "post.php?id=" . $post_id;  // Supposons que votre script pour voir un post est "post.php" et accepte un paramètre "id"
            $sql = "INSERT INTO notifications (user_id, content, link, status, created_at, updated_at) 
                    VALUES (?, ?, ?, 'unread', NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$post_owner_id, $notification_content, $link_to_post]);
            
            header('Location: dashboard.php');
        } else {
            echo "Erreur : Le commentaire n'a pas pu être ajouté!";
        }
    } else {
        echo "Erreur : tous les champs sont nécessaires!";
    }
}

// Test quand il y a un bug
echo "POST: ";
var_dump($_POST);
echo "SESSION: ";
var_dump($_SESSION);
?>
