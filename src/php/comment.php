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

// On met le user_id en session
$_SESSION['user_id'] = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['id'];
    $content = $_POST['content'];

    $sql = "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$content, $post_id, $user_id]);

    header('Location: dashboard.php');
    exit();
} else {
    $post_id = $_GET['id'];
}

$sql = "SELECT content FROM posts WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

function add_comment($pdo, $post_id, $user_id, $content) {
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$post_id, $user_id, $content]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['post_id'], $_POST['content'], $_SESSION['user_id'])) {
        // Sanitize and validate input
        $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
        $user_id = $_SESSION['user_id'];
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);

        if (filter_var($post_id, FILTER_VALIDATE_INT) === false || empty($content)) {
            echo "Erreur : tous les champs sont nécessaires et doivent être valides!";
        } else {
            if(add_comment($pdo, $post_id, $user_id, $content)){
                echo "Commentaire ajouté avec succès!";
            } else {
                echo "Erreur : Le commentaire n'a pas pu être ajouté!";
            }
        }
    } else {
        echo "Erreur : tous les champs sont nécessaires!";
    }
} else {
    echo "Erreur : la méthode de requête non valide!";
}

echo "POST: ";
var_dump($_POST);
echo "SESSION: ";
var_dump($_SESSION);
?>
