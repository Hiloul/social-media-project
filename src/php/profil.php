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

// Vérification de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $bio = $_POST['bio'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';

    // Si un formulaire de modification du profil a été soumis
    if (!empty($bio) || !empty($birthdate)) {
        // Si une nouvelle photo a été téléchargée
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $file_tmp = $_FILES['profile_picture']['tmp_name'];
            $file_name = $_FILES['profile_picture']['name'];

            // Vérifiez si le fichier a été correctement téléchargé
            if (is_uploaded_file($file_tmp)) {
                // Déplacez le fichier dans le répertoire souhaité
                $destination = 'uploads/' . $file_name;
                if (move_uploaded_file($file_tmp, $destination)) {
                    // Si le fichier a été correctement déplacé, enregistrez le chemin du fichier
                    $profile_picture = $destination;
                }
            }
        }

        // Récupération des informations du profil
        $sql = "SELECT profile_picture FROM profils WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $profil = $stmt->fetch();

        if (empty($profile_picture)) {
            $profile_picture = $profil['profile_picture'];
        }

        // Mettre à jour le profil si un profil existe déjà
        $sql = "UPDATE profils SET profile_picture = ?, bio = ?, birthdate = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$profile_picture, $bio, $birthdate, $user_id]);
    }

    // Récupérer les informations du profil
    $sql = "SELECT * FROM profils WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $profil = $stmt->fetch();
    // Si une recherche a été soumise
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $_POST['search'];

        // Requête pour la recherche
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE :search");
        $stmt->execute(['search' => "%$search%"]);

        // Récupération des résultats
        $results = $stmt->fetchAll();

        // Affichage des résultats
        foreach ($results as $row) {
            echo $row['username'] . "<br>";
        }
    }
}

// Récupération des informations du profil après la mise à jour
$sql = "SELECT id, user_id, profile_picture, bio, birthdate, created_at, updated_at FROM profils WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
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

// Vérification de la soumission du formulaire chercher user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search']) && trim($_POST['search']) !== '') {
        $search = $_POST['search'];

        // Requête pour la recherche
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE :search");
        $stmt->execute(['search' => "%$search%"]);

        // Récupération des résultats
        $results = $stmt->fetchAll();

        // Affichage des résultats
        foreach ($results as $row) {
            echo "<p>" . htmlspecialchars($row['username']) . "</p>";

            // Requête pour vérifier si une demande d'ami a déjà été envoyée
            $stmt = $pdo->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ?");
            $stmt->execute([$_SESSION['user_id'], $row['id']]);
            $friend_request = $stmt->fetch();

            if ($friend_request) {
                echo "<p>Demande d'amitié envoyée</p>";
            } else {
                // Afficher le bouton d'ajout d'ami
                echo "<form action='send_friend_request.php' method='POST'>";
                echo "<input type='hidden' name='friend_id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<input type='submit' value='Envoyer une demande ami'>";
                echo "</form>";
            }
        }
    } else if (isset($_POST['friend_id'])) {
        $friend_id = $_POST['friend_id'];
        $user_id = $_SESSION['user_id'];

        // Ajoute la demande d'ami dans la table 'friends'
        $sql = "INSERT INTO friends (user_id, friend_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_id]);

        // Crée une notification pour l'utilisateur qui reçoit la demande d'ami
        $content = "Vous avez reçu une demande d'ami de " . $_SESSION['username'];
        $sql = "INSERT INTO notifications (user_id, content, status, created_at, updated_at) 
                VALUES (?, ?, 0, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$friend_id, $content]);

        // Redirige l'utilisateur vers la page précédente
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

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
<html>

<head>
    <title>Profil de <?= htmlspecialchars($_SESSION['username']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E9EBEE;
            /* Gris clair à la Facebook */
            color: #1C1E21;
            /* Gris foncé pour le texte */
            margin: 0;
        }

        .menu {
            margin-top: 10px;
            height: 60px;
            background: #FFFFFF;
            display: flex;
            justify-content: end;
            align-items: center;
            border-radius: 20px;
            border: 1px solid #D4D6D8;
            /* Bordure subtile */
        }

        .container {
            display: flex;
            margin: 30px;
            justify-content: space-between;
        }

        .block-1,
        .block-2 {
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
        }

        .block-1 {
            height: 400px;
            width: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
            margin-bottom: 20px;
        }

        .block-2 {
            width: 60%;
        }

        h1,
        h2 {
            color: #1C1E21;
            margin-left: 10px;
        }

        p {
            margin-left: 10px;
        }

        img {
            max-width: 100px;
            border-radius: 50%;
        }

        button {
            background-color: #1877F2;
            /* Bleu Facebook */
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.2s;
            /* Effet de transition */
        }

        button:hover {
            background-color: #165EAB;
            /* Bleu un peu plus foncé lors du survol */
        }

        a {
            color: #1877F2;
            /* Bleu Facebook */
            margin-right: 15px;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
            /* souligné lors du survol */
        }

        .content {
            margin-bottom: 30px;
        }

        /* Responsive */
        @media screen and (max-width: 1595px) {
            .container {
                display: flex;
                flex-direction: column;
                margin: 30px;
                justify-content: center;
                align-items: center;
            }

            .block-1 {
                height: 400px;
                width: 90%;
                margin-right: 0;
                padding: 0;
            }

            .block-2 {
                width: 100%;
            }
        }

        .burger-menu,
        .burger-menu2 {
            position: fixed;
            top: 0;
            height: 100vh;
            padding: 20px;
            background-color: #3b5998;
            /* Bleu foncé */
            color: #FFFFFF;
            overflow-y: auto;
            font-family: Arial, sans-serif;
            box-shadow: -2px 0px 5px rgba(0, 0, 0, 0.1);
            z-index: 999;
        }

        .burger-menu h2,
        .burger-menu2 h2 {
            color: #FFFFFF;
            margin-bottom: 20px;
        }

        .burger-menu a,
        .burger-menu2 a {
            color: #FFFFFF;
            text-decoration: none;
        }

        .burger-menu-btn {
            position: fixed;
            right: 20px;
            top: 20px;
            z-index: 1000;
        }

        .scroll-container {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        .scroll-container::-webkit-scrollbar {
            width: 8px;
        }

        .scroll-container::-webkit-scrollbar-track {
            background: #F0F2F5;
        }

        .scroll-container::-webkit-scrollbar-thumb {
            background-color: #BEC3C9;
            border-radius: 10px;
        }

        .scroll-container::-webkit-scrollbar-thumb:hover {
            background: #AAB2BD;
        }

        .scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #BEC3C9 #F0F2F5;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <div class="container">
        <nav class="menu">
            <form action="profil.php" method="POST">
                <!-- Rechercher: -->
                <label for="search"><i class="fa-solid fa-magnifying-glass"></i></label>
                <input type="text" id="search" placeholder="Rechercher..." name="search" required>
                <input type="submit" value="Ok">
            </form>
        </nav>

        <div class="afficher_profil_recherche">

        </div>
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

            <div class="burger-menu2" id="burgerMenu2">
                <h2>Paramètres</h2>
                <a href="edit_profil.php">Mise à jour du profil</a>
                <a href="change_password.php">Changer le mot de passe</a>
                <a href="privacy_settings.php">Paramètres de confidentialité</a>
                <a href="notification.php">Paramètres de notification</a>
                <a href="profil.php">Retour</a>
            </div>
            <!-- Accueil  -->
            <a href="dashboard.php"><i class="fa-solid fa-house"></i></a>
            <!-- Messagerie privée -->
            <a href="message.php"><i class="fa-solid fa-envelope"></i></a>
            <!-- Boutton cloche et rouage -->
            <button class="burger-menu-btn" id="burgerMenuBtn"><i class="fas fa-bell"></i></button>
            <button id="burgerButtonSettings"><i class="fa-solid fa-gear"></i></button>
        </div>
        <div class="block-1">
            <?php if ($profil) : ?>
                <p></strong><img src="<?= htmlspecialchars($profil['profile_picture']) ?>" alt="Profile Picture"></p>
                <h1>@<?= htmlspecialchars($_SESSION['username']) ?></h1>
                <form action="edit_profil.php">
                    <button type="submit">Modifier Profil</button>
                </form>
                <p><strong>Biographie : </strong><?= htmlspecialchars($profil['bio']) ?></p>
                <p><strong>Date de naissance : </strong><?= date("d-m-Y", strtotime($profil['birthdate'])) ?></p>
                <p><strong>Créé depuis le : </strong><?= date("d-m-Y H:i", strtotime($profil['created_at'])) ?></p>
            <?php else : ?>
                <p>Aucune information de profil à afficher.</p>
                <form action="edit_profil.php">
                    <button type="submit">Créer Profil</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="block-2">
            <!-- Amis -->
            <h2><i class="fa-solid fa-address-book"></i> Mes amis</h2>
            <div class="scroll-container">
                <?php if (!empty($friends)) : ?>
                    <?php foreach ($friends as $friend) : ?>
                        <div class="friend content">
                            <p><?= htmlspecialchars($friend['username']) ?></p>
                            <!-- Bouton de suppression d'ami. Cela envoie une requête GET à votre script. -->
                            <form action="status_friend.php" method="get">
                                <input type="hidden" name="delete_friend" value="<?php echo $friend['id']; ?>">
                                <button type="submit">Supprimer l'ami</button>
                            </form>
                            <!-- Bouton de blocage d'ami. Cela envoie une requête POST à votre script. -->
                            <form action="status_friend.php" method="post">
                                <input type="hidden" name="friend_id" value="<?php echo $friend['id']; ?>">
                                <button type="submit">Bloquer l'ami</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>Aucun ami. Prend un curly</p>
                <?php endif; ?>
            </div>

            <!-- Posts -->
            <h2><i class="fa-regular fa-keyboard"></i> Mes Posts</h2>
            <div class="scroll-container">
                <?php if (!empty($posts)) : ?>
                    <?php foreach ($posts as $post) : ?>
                        <div class="post content">
                            <p><?= htmlspecialchars($post['content']) ?></p>
                            <p>Publié le <?= date("d-m-Y H:i", strtotime($post['created_at'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>Aucun post à afficher.</p>
                <?php endif; ?>
                <!-- Like -->
                <h2><i class="fa-regular fa-thumbs-up"></i> Mes Likes</h2>
                <?php if (!empty($likes)) : ?>
                    <?php foreach ($likes as $like) : ?>
                        <div class="like content">
                            <p><?= htmlspecialchars($like['content']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>Aucun like à afficher.</p>
                <?php endif; ?>
            </div>

            <!-- Commentaires -->
            <h2><i class="fa-regular fa-comment"></i> Mes Commentaires</h2>
            <?php if (!empty($comments)) : ?>
                <?php foreach ($comments as $comment) : ?>
                    <div class="comment content">
                        <p><?= htmlspecialchars($comment['content']) ?></p>
                        <p>Commenté le <?= date("d-m-Y H:i", strtotime($comment['created_at'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun commentaire à afficher.</p>
            <?php endif; ?>
        </div>
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