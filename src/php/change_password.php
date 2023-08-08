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
    // Vérifiez si les deux mots de passe correspondent
    if ($_POST['password'] != $_POST['confirm_password']) {
        echo "Les mots de passe ne correspondent pas.";
        exit();
    }

    // Chiffrez le nouveau mot de passe
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe dans la base de données
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_password, $user_id]);

    echo "Le mot de passe a été mis à jour avec succès.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Changer le mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        form {
            background-color: #fff;
            padding: 20px;
            width: 300px;
            margin: 0 auto;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            color: #fff;
            background-color: #3b5998;
            cursor: pointer;
        }
        button:hover {
            background-color: #4a69bd;
        }
        h1 {
            text-align: center;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <h1>Changer le mot de passe</h1>
    <form method="POST">
        <label for="password">Nouveau mot de passe:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirmez le nouveau mot de passe:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Changer le mot de passe</button>
    </form>
    <a href="profil.php">Retour</a>
</body>
</html>

