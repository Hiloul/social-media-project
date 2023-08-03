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
        if($stmt->execute([$post_id, $user_id, $content])){
            echo "Commentaire ajouté avec succès!";
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
