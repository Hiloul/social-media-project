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

// Si un formulaire de modification du profil a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mettre à jour le profil si un profil existe déjà
    if ($profil) {
        $sql = "UPDATE profils SET profile_picture = ?, bio = ?, birthdate = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$profile_picture, $bio, $birthdate, $user_id]);
    }
    // Créer un nouveau profil s'il n'existe pas encore
    else {
        $sql = "INSERT INTO profils (user_id, profile_picture, bio, birthdate, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $profile_picture, $bio, $birthdate]);
    }
}

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


// Obtenir les posts de l'utilisateur
$sql = "SELECT * FROM posts WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();

// Obtenir les likes de l'utilisateur
$sql = "SELECT posts.content FROM likes INNER JOIN posts ON likes.post_id = posts.id WHERE likes.user_id = ?";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
            color: #333;
        }

        h1,
        h2 {
            color: #444;
        }

        .post,
        .like,
        .comment,
        .friend {
            background-color: white;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0px 0px 5px 0px rgba(0, 0, 0, 0.1);
        }

        .post p,
        .like p,
        .comment p,
        .friend p {
            margin: 0 0 10px;
        }

        img {
            max-width: 100px;
            border-radius: 50%;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        a {
            color: #007BFF;
        }

        /* Responsive  */
        @media only screen and (max-width: 600px) {

            .post,
            .like,
            .comment,
            .friend {
                padding: 10px;
            }

            img {
                max-width: 80px;
            }

            button {
                padding: 8px 16px;
            }
        }

        @media only screen and (max-width: 400px) {
            img {
                max-width: 60px;
            }

            button {
                padding: 6px 12px;
            }
        }
    </style>
</head>

<body>
    <h1><?= htmlspecialchars($_SESSION['username']) ?></h1>

    <?php if ($profil) : ?>
        <p></strong><img src="<?= htmlspecialchars($profil['profile_picture']) ?>" alt="Profile Picture"></p>
        <form action="edit_profil.php">
            <button type="submit">Modifier Profil</button>
        </form>
        <p><strong>Biographie : </strong><?= htmlspecialchars($profil['bio']) ?></p>
        <p><strong>Date de naissance : </strong><?= date("d-m-Y", strtotime($profil['birthdate'])) ?></p>
        <p><strong>Créé depuis le : </strong><?= date("d-m-Y H:i", strtotime($profil['created_at'])) ?></p>
    <?php else : ?>
        <p>Aucune information de profil à afficher.</p>
    <?php endif; ?>

    <a href="dashboard.php">Aller à l'accueil</a>
    <a href="message.php">Messagerie privée</a>

    <h2>Mes amis</h2>
    <?php if (!empty($friends)) : ?>
        <?php foreach ($friends as $friend) : ?>
            <div class="friend">
                <p><?= htmlspecialchars($friend['username']) ?></p>
                <p><button>Supprimer l'ami</button></p>
                <p><button>Bloquer l'ami</button></p>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Aucun ami.</p>
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

    <h2>Mes Likes</h2>
    <?php if (!empty($likes)) : ?>
        <?php foreach ($likes as $like) : ?>
            <div class="like">
                <p><?= htmlspecialchars($like['content']) ?></p>
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