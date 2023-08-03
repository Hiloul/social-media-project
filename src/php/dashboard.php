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

// Récupérer tous les posts, les informations de l'utilisateur qui les a publiés, et le nombre total de likes
$sql = "
    SELECT posts.id, posts.content, posts.created_at, users.username, COUNT(likes.id) as likes 
    FROM posts 
    INNER JOIN users ON posts.user_id = users.id 
    LEFT JOIN likes ON posts.id = likes.post_id
    GROUP BY posts.id
";
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

if (isset($_GET['like'])) {
    $post_id = $_GET['like'];

    // Vérifier si l'utilisateur a déjà aimé ce post
    $sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $post_id]);

    if ($stmt->rowCount() === 0) {
        // L'utilisateur peut aimer le post
        $sql = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $post_id]);
    }
    // Rediriger l'utilisateur vers le tableau de bord après avoir liké le post
    header('Location: dashboard.php');
    exit();
}

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

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

// Récupérer tous les posts, les informations de l'utilisateur qui les a publiés, et le nombre total de likes
$sql = "
    SELECT posts.id, posts.content, posts.created_at, users.username, COUNT(likes.id) as likes 
    FROM posts 
    INNER JOIN users ON posts.user_id = users.id 
    LEFT JOIN likes ON posts.id = likes.post_id
    GROUP BY posts.id
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();

// Pour chaque post, récupérer les commentaires
foreach ($posts as &$post) {
    // Initialize comments key to an empty array
    $post['comments'] = [];

    $sql = "
        SELECT comments.content, comments.created_at, users.username 
        FROM comments 
        INNER JOIN users ON comments.user_id = users.id 
        WHERE comments.post_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post['id']]);
    $comments = $stmt->fetchAll();

    if (!empty($comments)) {
        $post['comments'] = $comments;
    }
}
unset($post);

// Pour chaque post, récupérer les commentaires
foreach ($posts as &$post) {
    $sql = "
        SELECT comments.id, comments.content, comments.created_at, users.username 
        FROM comments 
        INNER JOIN users ON comments.user_id = users.id 
        WHERE comments.post_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post['id']]);
    $comments = $stmt->fetchAll();

    $post['comments'] = $comments;
}
unset($post); //

if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $sql = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$comment_id, $user_id]);

    // Rediriger l'utilisateur vers le tableau de bord après avoir supprimé le commentaire
    header('Location: dashboard.php');
    exit();
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
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f0f0f0;
            color: #333;
        }
        h1, h3, h4 {
            color: #444;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            color: #0056b3;
        }
        .post {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        footer {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60px;
            background-color: #333;
            color: #fff;
            font-size: 14px;
            margin-top: 20px;
            
            bottom: 0;
            width: 100%;
        }
        footer::before {
            content: "\00a9";
            margin-right: 5px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
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
        <a href="profil.php">Profil</a>
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
                <p><?= $post['likes'] ?> likes</p>
                <a href="dashboard.php?like=<?= $post['id'] ?>">Like</a>
                <?php if ($_SESSION['username'] === $post['username']) : ?>
                    <a href="dashboard.php?delete=<?= $post['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">Supprimer</a>
                    <a href="edit_post.php?id=<?= $post['id'] ?>">Modifier</a>
                <?php endif; ?>

              <!-- Affichage des commentaires -->
<div class="comments">
    <?php if (empty($post['comments'])) : ?>
        <p>0 commentaires</p>
    <?php else : ?>
        <?php foreach ($post['comments'] as $comment) : ?>
            <div class="comment">
                <p><?= $comment['content'] ?></p>
                <p>Commentaire par <?= $comment['username'] ?> le <?= date("d-m-Y H:i", strtotime($comment['created_at'])) ?></p>
                <?php if ($_SESSION['username'] === $comment['username']) : ?>
                    <a href="dashboard.php?delete_comment=<?= $comment['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">Supprimer</a>
                    <a href="edit_comment.php?id=<?= $comment['id'] ?>">Modifier</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

                <!-- Formulaire de commentaire -->
                <form id="commentForm" method="post" action="comment.php">
                    <input type="hidden" id="post_id" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
                    <textarea id="content" name="content" placeholder="Ajouter un commentaire..." required></textarea>
                    <button type="submit">Publier le commentaire</button>
                </form>

            <?php endforeach; ?>

            </div>
            <footer>Social Media &copy;2023</footer>
</body>
</html>