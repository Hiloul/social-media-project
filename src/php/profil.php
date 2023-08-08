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


// Vérification de la soumission du formulaire
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
            echo "<a href='profil.php?user_id=".$row['id']."'>".$row['username']."</a><br>";
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Profil de <?= htmlspecialchars($_SESSION['username']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
        }

        .menu {
            margin-top: 10px;
            height: 60px;
            background: white;
            display: flex;
            justify-content: end;
            align-items: center;
            border-radius: 20px;
        }

        .container {
            display: flex;
            margin: 30px;
            justify-content: space-between;
        }

        .block-1 {
            height: 400px;
            border-radius: 20px;
            width: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            align-items: center;
            margin-right: 10px;
            margin-bottom: 20px;
        }

        .block-2 {
            width: 60%;
            background: white;
            border-radius: 20px;
        }

        h1,
        h2 {
            color: #444;
            margin-left: 10px;
        }

        p {
            margin-left: 10px;
        }

        /* .post,
        .like,
        .comment,
        .friend {
            background-color: white;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0px 0px 5px 0px rgba(0, 0, 0, 0.1);
        } */
        /* .post p,
        .like p,
        .comment p,
        .friend p {
            margin: 0 0 10px;
        } */
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
            margin-right: 15px;
        }

        .content {
            margin-bottom: 30px;
        }

        /* Responsive  */
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
                border-radius: 20px;
                width: 90%;
                display: flex;
                flex-direction: column;
                justify-content: center;
                background: white;
                align-items: center;
                /* margin-right: 10px; */
                margin-bottom: 20px;
                padding: 0;
            }

            .block-2 {
                width: 100%;
            }
        }

        /* @media only screen and (max-width: 600px) {

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
        } */
    </style>
</head>

<body>
    <nav class="menu">
        <a href="dashboard.php">Aller à l'accueil</a>
        <a href="message.php">Messagerie privée</a>
        <form action="profil.php" method="POST">
            <label for="search">Rechercher:</label>
            <input type="text" id="search" placeholder="Rechercher..." name="search" required>
            <input type="submit" value="Recherche">
        </form>
    </nav>

    <div class="afficher_profil_recherche">

    </div>

    <div class="container">
    <div class="block-1">
    <?php if ($profil) : ?>
        <p></strong><img src="<?= htmlspecialchars($profil['profile_picture']) ?>" alt="Profile Picture"></p>
        <h1><?= htmlspecialchars($_SESSION['username']) ?></h1>
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
            <h2>Mes amis</h2>
            <?php if (!empty($friends)) : ?>
                <?php foreach ($friends as $friend) : ?>
                    <div class="friend content">
                        <p><?= htmlspecialchars($friend['username']) ?></p>
                        <p>
                            <button onclick="confirmAction('Êtes-vous sûr de vouloir supprimer cet ami(e) ?', 'status_friend.php?delete_friend=<?= htmlspecialchars(intval($friend['id']), ENT_QUOTES, 'UTF-8') ?>')">Supprimer l'ami</button>
                        </p>
                        <p>
                            <button onclick="confirmAction('Êtes-vous sûr de vouloir bloquer cet ami(e) ?', 'status_friend.php?block_friend=<?= htmlspecialchars(intval($friend['id']), ENT_QUOTES, 'UTF-8') ?>')">Bloquer l'ami</button>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun ami. Prend un curly</p>
            <?php endif; ?>

            <h2>Mes Posts</h2>
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

            <h2>Mes Likes</h2>
            <?php if (!empty($likes)) : ?>
                <?php foreach ($likes as $like) : ?>
                    <div class="like content">
                        <p><?= htmlspecialchars($like['content']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun like à afficher.</p>
            <?php endif; ?>

            <h2>Mes Commentaires</h2>
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
</body>

</html>