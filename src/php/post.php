<?php
session_start();
require 'dbconfig.php';

// Récupérer l'ID de l'utilisateur à partir du nom d'utilisateur
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

// Maintenant $user['id'] contient l'ID de l'utilisateur
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($user_id, $_POST['content'])) {
        $content = $_POST['content'];

        if (strlen($content) <= 280) { // Longueur de la publication
            $sql = "INSERT INTO posts (user_id, content) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $content]);
            
            // Rediriger l'utilisateur vers le tableau de bord après la publication
            header('Location: dashboard.php');
            exit();
        } else {
            echo "Le post dépasse la limite de 280 caractères.";
        }
    } else {
        echo "Veuillez vous connecter et écrire / publier quelque chose.";
    }
}
?>
