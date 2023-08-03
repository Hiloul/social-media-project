<?php
session_start();
require 'dbconfig.php';

// Assurez-vous que l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur à partir de la session
$user_id = $_SESSION['user_id'];

// Obtenir les informations du profil
$sql = "SELECT * FROM profils WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$profil = $stmt->fetch();

// Obtenir les posts de l'utilisateur
$sql = "SELECT * FROM posts WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();

// Obtenir les likes de l'utilisateur
$sql = "SELECT * FROM likes WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$likes = $stmt->fetchAll();

// Obtenir les commentaires de l'utilisateur
$sql = "SELECT * FROM comments WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$comments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil de <?= htmlspecialchars($_SESSION['username']) ?></title>
</head>
<body>
    <h1>Profil de <?= htmlspecialchars($_SESSION['username']) ?></h1>

    <h2>Informations de profil</h2>

<?php if ($profil) : ?>
    <p><strong>Bio : </strong><?= htmlspecialchars($profil['bio']) ?></p>
    <p><strong>Localisation : </strong><?= htmlspecialchars($profil['location']) ?></p>
    <p><strong>Site web : </strong><a href="<?= htmlspecialchars($profil['website']) ?>"><?= htmlspecialchars($profil['website']) ?></a></p>
    <p><strong>Date de naissance : </strong><?= date("d-m-Y", strtotime($profil['birthdate'])) ?></p>
<?php else : ?>
    <p>Aucune information de profil à afficher.</p>
<?php endif; ?>


<h2>Mes Posts</h2>
<?php if (!empty($posts)) : ?>
    <?php foreach ($posts as $post) : ?>
        <div class="post">
            <p><?= htmlspecialchars($post['content']) ?></p>
            <p>Publié le <?= date("d-m-Y H:i", strtotime($post['created_at'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>Aucun post à afficher.</p>
<?php endif; ?>

<!-- A ce niveau modifier l'affichage -->
<h2>Mes Likes</h2>
<?php if (!empty($likes)) : ?>
    <?php foreach ($likes as $like) : ?>
        <div class="like">
            <p>Post ID: <?= htmlspecialchars($like['post_id']) ?></p>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>Aucun like à afficher.</p>
<?php endif; ?>

<h2>Mes Commentaires</h2>
<?php if (!empty($comments)) : ?>
    <?php foreach ($comments as $comment) : ?>
        <div class="comment">
            <p><?= htmlspecialchars($comment['content']) ?></p>
            <p>Commenté le <?= date("d-m-Y H:i", strtotime($comment['created_at'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>Aucun commentaire à afficher.</p>
<?php endif; ?>


</body>
</html>
