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

// Récupérer les notifications pour l'utilisateur actuel
$sql = "SELECT * FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$newNotifications = $stmt->fetchAll();

// Récupérer les notifications pour l'utilisateur actuel
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Gérer la suppression d'une notification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_notification_id'])) {
  $delete_notification_id = $_POST['delete_notification_id'];

  // Supprimer la notification de la base de données
  $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$delete_notification_id, $user_id]);

  // Rediriger l'utilisateur vers la page précédente
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Tableau de bord</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f0f0f0;
            color: #333;
        }

        h1,
        h3,
        h4 {
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
            resize: none;
            outline: none;
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

        .burger-menu {
            width: 300px;
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            padding: 20px;
            background-color: #3b5998;
            color: #fff;
            overflow-y: auto;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            font-family: Arial, sans-serif;
            box-shadow: -2px 0px 5px 0px rgba(0, 0, 0, 0.1);
            z-index: 999;
        }

        .burger-menu h2 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .burger-menu a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            margin-top: 20px;
            display: block;
        }

        .burger-menu .notification {
            background-color: #4a69bd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .burger-menu .notification.unread {
            background-color: #6a89cc;
        }

        .burger-menu .notification p {
            margin: 0;
            font-size: 16px;
            line-height: 1.5;
        }

        .burger-menu-btn {
            position: fixed;
            right: 20px;
            top: 20px;
            z-index: 1000;
        }

        .burger-menu2 {
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            padding: 20px;
            background-color: #3b5998;
            color: #fff;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            font-family: Arial, sans-serif;
            box-shadow: 2px 0px 5px 0px rgba(0, 0, 0, 0.1);
        }

        .burger-menu2.open {
            transform: translateX(0);
        }

        .burger-menu2 h2 {
            color: #fff;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .burger-menu2 a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            line-height: 2.5;
            display: block;
        }

        .burger-menu2 a:hover {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 2px 10px;
        }

        .new-notification-dot {
            height: 10px;
            width: 10px;
            background-color: red;
            border-radius: 50%;
            display: inline-block;
        }
        .posts-container {
            max-height: 600px; /* définissez la hauteur maximale en fonction de vos besoins */
            overflow-y: auto; /* défilement vertical lorsque le contenu dépasse la hauteur maximale */
            padding: 10px;
            scroll-behavior: smooth;
        }

        /* Personnalisation de la barre de défilement pour les navigateurs basés sur Chromium */
        .posts-container::-webkit-scrollbar {
            width: 12px;
        }

        .posts-container::-webkit-scrollbar-track {
            background: #f0f2f5;
        }

        .posts-container::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 20px;
            border: 3px solid #f0f2f5;
        }

        .posts-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Personnalisation de la barre de défilement pour Firefox */
        .posts-container {
            scrollbar-width: thin;
            scrollbar-color: #888 #f0f2f5;
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

    <div class="notif">
        <div class="burger-menu" id="burgerMenu">
            <h2>Notifications</h2>
            <?php if (!empty($notifications)) : ?>
                <?php foreach ($notifications as $notification) : ?>
                    <div class="notification <?= $notification['status'] == 0 ? 'unread' : 'read' ?>">
                        <p><?= htmlspecialchars($notification['content']) ?></p>
                        <p><?= date("d-m-Y H:i", strtotime($notification['created_at'])) ?></p>
                        <!-- Ajout du formulaire et du bouton de suppression de notification -->
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                            <input type="hidden" name="delete_notification_id" value="<?= $notification['id'] ?>">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucune notification</p>
            <?php endif; ?>
            <a href="profil.php">Retour</a>
        </div>
        <!-- Boutton cloche et rouage -->
        <button class="burger-menu-btn" id="burgerMenuBtn">
            <i class="fas fa-bell"></i>
            <!-- Point rouge pour les nouvelles notifications -->
            <?php if (!empty($newNotifications)) : ?>
                <span class="new-notification-dot"></span>
            <?php endif; ?>
        </button>
        
        <div class="burger-menu2" id="burgerMenu2">
            <h2>Paramètres</h2>
            <a href="edit_profil.php">Mise à jour du profil</a>
            <a href="change_password.php">Changer le mot de passe</a>
            <a href="privacy_settings.php">Paramètres de confidentialité</a>
            <a href="notification.php">Paramètres de notification</a>
            <a href="dashboard.php">Retour</a>
            <br>
            <a href="logout.php">Déconnecter</a>
        </div>

        <div class="container">
            <h3>Mon tableau de bord: </h3>
            <!-- Profil icon -->
            <a href="profil.php"><button><i class="fa-solid fa-user"></i></button></a>
            <!-- Parametre burger icon -->
            <button id="burgerButtonSettings"><i class="fa-solid fa-gear"></i></button>
            <!-- Decconexion icon -->
            <a href="logout.php"><button><i class="fa-solid fa-right-from-bracket"></i></button></a>

            <div class="publication">
                <h4>Publier quelque chose: </h4>
                <form id="postForm" method="POST" action="post.php">
                    <textarea name="content" id="content" rows="5" required maxlength="280" placeholder="Quoi de neuf ?"></textarea>
                    <br>
                    <br>
                    <button type="submit">Publier</button>
                </form>
            </div>
                
            <div class="posts-container">
            <?php foreach ($posts as $post) : ?>
                <div class="post">
                    <h2><?= $post['content'] ?></h2>
                    <p>Publié par <?= $post['username'] ?> le <?= date("d-m-Y H:i", strtotime($post['created_at'])) ?></p>
                    <p><?= $post['likes'] ?> <i class="fas fa-thumbs-up"></i></p>
                    <!-- Like icon pouce -->
                    <a href="dashboard.php?like=<?= $post['id'] ?>"><i class="fas fa-thumbs-up"></i></a>
                    <?php if ($_SESSION['username'] === $post['username']) : ?>
                        <!-- Modifier icon  -->
                        <a href="edit_post.php?id=<?= $post['id'] ?>"><i class="fas fa-edit"></i></a>
                        <!-- Supprimer icon poubelle -->
                        <a href="dashboard.php?delete=<?= $post['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')"><i class="fa-solid fa-trash"></i></a>
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
                                        <!-- Icon supprimer -->
                                        <a href="dashboard.php?delete_comment=<?= $comment['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')"><i class="fa-solid fa-trash"></i></a>
                                        <!-- Icon modifier -->
                                        <a href="edit_comment.php?id=<?= $comment['id'] ?>"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Formulaire de commentaire -->
                    <form id="commentForm" method="post" action="comment.php">
                        <input type="hidden" id="post_id" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
                        <textarea id="content" name="content" placeholder="Ajouter un commentaire..." required></textarea>
                        <!-- Commentaire icon -->
                        <button type="submit">Publier le commentaire  <i class="fa-solid fa-comment"></i></button>
                    </form>

                <?php endforeach; ?>

                </div>
            </div>
                <footer>Social Media &copy;2023</footer>
                <!-- Script burger menu -->
                <script>
                    document.getElementById('burgerMenuBtn').addEventListener('click', function() {
                        var burgerMenu = document.getElementById('burgerMenu');

                        if (burgerMenu.style.transform === 'translateX(0px)') {
                            burgerMenu.style.transform = 'translateX(100%)';
                        } else {
                            burgerMenu.style.transform = 'translateX(0)';
                        }
                    });

                    var burgerMenu2 = document.getElementById('burgerMenu2');
                    var burgerButton2 = document.getElementById('burgerButtonSettings');

                    burgerButton2.addEventListener('click', function() {
                        burgerMenu2.classList.toggle('open');
                    });
                </script>

</body>

</html>