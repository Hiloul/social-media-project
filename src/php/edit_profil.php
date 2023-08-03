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

// Récupérer les données du formulaire
$bio = isset($_POST['bio']) && !empty($_POST['bio']) ? $_POST['bio'] : $profil['bio'];
$birthdate = isset($_POST['birthdate']) && !empty($_POST['birthdate']) ? $_POST['birthdate'] : $profil['birthdate'];
// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $bio = isset($_POST['bio']) && !empty($_POST['bio']) ? $_POST['bio'] : $profil['bio'];
    $birthdate = isset($_POST['birthdate']) && !empty($_POST['birthdate']) ? $_POST['birthdate'] : $profil['birthdate'];

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
    } else {
        $profile_picture = $profil['profile_picture'];
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
    <title>Modifier profil</title>
</head>
<body>
    <h1>Modifier Profil</h1>

    <form method="POST" enctype="multipart/form-data">
        <label for="bio">Bio :</label>
        <textarea id="bio" name="bio"><?= htmlspecialchars($profil['bio']) ?></textarea>

        <label for="birthdate">Date de naissance :</label>
        <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($profil['birthdate']) ?>">

        <label for="profile_picture">Photo de profil :</label>
        <input type="file" id="profile_picture" name="profile_picture">

        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
