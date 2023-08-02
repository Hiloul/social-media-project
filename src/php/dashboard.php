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

// Récupérer tous les posts et les informations de l'utilisateur qui les a publiés
$sql = "SELECT posts.id, posts.content, posts.created_at, users.username FROM posts INNER JOIN users ON posts.user_id = users.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();

if (isset($_GET['delete'])) {
    $post_id = $_GET['delete'];
    $sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $user_id]);

    // Rediriger l'utilisateur vers le tableau de bord après avoir supprimé le post
    header('Location: dashboard.php');
    exit();
}

$post_id = $_GET['id'] ?? null; // Utilisation de l'opérateur de fusion null

if ($post_id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $content = $_POST['content'];
        $sql = "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$content, $post_id, $user_id]);
        header('Location: dashboard.php');
        exit();
    }

    $sql = "SELECT content FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $user_id]);
    $post = $stmt->fetch();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <style>
        body {
            background: linear-gradient(to bottom, #ff69b4, #ffb6c1, #ffffff);
            height: 100vh;
            margin: 0;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 36px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 200px;
            margin-top: 200px;
        }

        h3 {
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            margin: 5px 0;
            padding: 10px 20px;
            text-decoration: none;
            color: #333;
            background-color: #ddd;
            border-radius: 5px;

        }

        .container:hover {
            background-color: #bbb;
        }

        footer {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60px;
            background-color: #333;
            color: #fff;
            font-size: 14px;
            width: 100%;
            bottom: 0;
        }

        footer::before {
            content: "\00a9";
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <h1>
        Bienvenue à toi,
        <?php
        if (isset($_SESSION['username'])) {
            echo $_SESSION['username'];
        } else {
            echo "Invité";
        }
        ?>!
    </h1>
    <div class="container">
        <h3>Mon tableau de bord: </h3>
        <a href="http://localhost/php/social-media-project/index.html">Accueil</a>
        <a href="logout.php">Déconnecter</a>

        <div class="publication">
            <h4>Publier quelque chose: </h4>
            <form id="postForm" method="POST" action="post.php">
                <textarea name="content" id="content" rows="5" required maxlength="280" placeholder="Quoi de neuf ?"></textarea>
                <br>
                <br>
                <button type="submit">Publier</button>
            </form>
        </div>

        <?php foreach ($posts as $post) : ?>
            <div class="post">
                <h2><?= $post['content'] ?></h2>
                <p>Publié par <?= $post['username'] ?> le <?= date("d-m-Y H:i", strtotime($post['created_at'])) ?></p>
                <?php if ($_SESSION['username'] === $post['username']) : ?>
                    <a href="dashboard.php?delete=<?= $post['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">Supprimer</a>
                    <a href="edit_post.php?id=<?= $post['id'] ?>">Modifier</a>

                <?php endif; ?>
            </div>
        <?php endforeach; ?>


    </div>
    <footer>Social Media &copy;2023</footer>
</body>

</html>