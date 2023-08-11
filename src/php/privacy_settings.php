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
$sql = "SELECT id, password FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

// Maintenant $user['id'] contient l'ID de l'utilisateur
$user_id = $user['id'];

// Récupérer les amis bloqués de l'utilisateur
$sql = "
    SELECT users.username
    FROM users
    JOIN friends ON users.id = friends.friend_id
    WHERE friends.user_id = ? AND friends.status = 'BLOCKED'
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$blocked_friends = $stmt->fetchAll();

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Vérifier le mot de passe
  if (password_verify($_POST['password'], $user['password'])) {
    // Supprimer le compte utilisateur de la base de données
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);

    // Détruire la session et rediriger l'utilisateur vers la page de connexion
    session_destroy();
    header('Location: http://localhost/php/social-media-project/');
    exit();
  } else {
    $error = "Mot de passe incorrect. Veuillez réessayer.";
  }
}

// Si un ami est à débloquer
if (isset($_POST['unblock_id'])) {
  $friend_id = $_POST['unblock_id'];

  // Mettre à jour le statut de l'ami dans la base de données
  $sql = "UPDATE friends SET status = 'NOT_FRIENDS' WHERE user_id = ? AND friend_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$user_id, $friend_id]);
}
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
      box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
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
    <li>
      Amis bloqués:
      <ul>
        <?php if (empty($blocked_friends)) : ?>
          <li>Aucun ami bloqué.</li>
        <?php else : ?>
          <?php foreach ($blocked_friends as $blocked_friend) : ?>
            <li>
              <?= htmlspecialchars($blocked_friend['username']) ?>
              <form action="" method="post">
                <input type="hidden" name="unblock_id" value="<?= $blocked_friend['friend_id'] ?>">
                <input type="submit" value="Débloquer">
              </form>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </li>
  </ul>

  <!-- Supprimer compte -->
  <form action="privacy_settings.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
    <input type="password" name="password" required>
    <button type="submit">Supprimer mon compte</button>
  </form>

  <a href="profil.php">Retour</a>
</body>
</html>
