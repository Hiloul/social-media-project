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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Paramètres privés</title>
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

     
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f2f5;
      padding: 20px;
    }

    h1 {
      font-size: 1.5rem;
      color: #4b4f56;
      margin-bottom: 1rem;
    }

    .list_privacy {
      list-style-type: none;
      padding: 0;
    }

    .list_privacy li {
      padding: 1rem;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
      margin-bottom: 1rem;
      font-size: 1rem;
      color: #4b4f56;
    }

    a {
      color: #385898;
      text-decoration: none;
      display: inline-block;
      margin-top: 1rem;
      font-size: 1rem;
    }

    /* Responsive Styles */
    @media screen and (max-width: 768px) {
      h1 {
        font-size: 1.25rem;
      }

      .list_privacy li {
        font-size: 0.875rem;
      }

      a {
        font-size: 0.875rem;
      }
    }
 
   </style>
</head>
<body>
    <h1>privacy_settings</h1>
    <ul class="list_privacy">
        <li>Voir ma liste de bloqué(s)</li>
        <li>Supprimer mon compte</li>
    </ul>
    


    <a href="profil.php">Retour</a>
</body>
</html>

