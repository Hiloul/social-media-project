<?php
session_start();
require 'dbconfig.php';

// Récupérer tous les posts et les informations de l'utilisateur qui les a publiés
$sql = "SELECT posts.content, posts.created_at, users.username FROM posts INNER JOIN users ON posts.user_id = users.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();

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
    </div>
<?php endforeach; ?>
</div>
    <footer>Social Media &copy;2023</footer>
</body>

</html>