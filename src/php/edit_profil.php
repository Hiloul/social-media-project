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

// Récupération des informations du profil
$sql = "SELECT id, user_id, profile_picture, bio, birthdate, created_at, updated_at FROM profils WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$profil = $stmt->fetch();
$bio = $birthdate = $profile_picture = '';

// Si le profil existe
if ($profil) {
    $bio = $profil['bio'];
    $birthdate = $profil['birthdate'];
    $profile_picture = $profil['profile_picture'];
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = isset($_POST['bio']) && !empty($_POST['bio']) ? $_POST['bio'] : $bio;
    $birthdate = isset($_POST['birthdate']) && !empty($_POST['birthdate']) ? $_POST['birthdate'] : $birthdate;

    // Si une nouvelle image a été uploadée, la traiter et la mettre à jour
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];

        // Check file size (5MB maximum)
        $max_size = 5 * 1024 * 1024;
        if ($file_size > $max_size) {
            die('The file is too large.');
        }

        // Check file type (only PNG, JPEG and GIF are allowed)
        $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            die('The file type is not allowed.');
        }

        // Upload the file
        $file_name_new = uniqid() . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $file_destination = 'uploads/' . $file_name_new;
        if (!move_uploaded_file($file_tmp, $file_destination)) {
            die('An error occurred while uploading the file.');
        }

        $profile_picture = $file_destination;
    }

    $updated_at = date('Y-m-d H:i:s');

    // Mise à jour des informations de profil
    $sql = "
        UPDATE profils 
        SET bio = ?, birthdate = ?, updated_at = ?, profile_picture = ? 
        WHERE user_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bio, $birthdate, $updated_at, $profile_picture, $user_id]);

    // Redirection vers la page de profil
    header('Location: profil.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input, textarea, button {
            width: 100%;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        button {
            background-color: #5C6BC0;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #3F51B5;
        }

        /* Media Queries for Responsiveness */
        @media screen and (max-width: 500px) {
            form {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <h1>Mon Profil</h1>

    <form method="POST" enctype="multipart/form-data">
        <label for="bio">Bio :</label>
        <textarea id="bio" name="bio"><?= htmlspecialchars($bio) ?></textarea>

        <label for="birthdate">Date de naissance :</label>
        <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($birthdate) ?>">

        <label for="profile_picture">Photo de profil :</label>
        <input type="file" id="profile_picture" name="profile_picture">

        <button type="submit">Enregistrer</button>
    </form>
        <a href="profil.php">Retour</a>
</body>
</html>
