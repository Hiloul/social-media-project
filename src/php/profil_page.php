<?php
session_start();
require 'dbconfig.php';

// Vérifier si un utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['username']]);
$current_user = $stmt->fetch();
$user_id = $current_user['id'];

if (!isset($_GET['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$profile_user_id = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);

$sql = "SELECT users.username, profils.id, profils.user_id, profils.profile_picture, profils.bio, profils.birthdate, profils.created_at, profils.updated_at 
        FROM profils 
        JOIN users ON users.id = profils.user_id 
        WHERE profils.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$profile_user_id]);
$profile = $stmt->fetch();

// Récupérer le statut d'amitié entre l'utilisateur actuel et le profil recherché
$sql = "SELECT status FROM friends WHERE user_id = ? AND friend_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $profile_user_id]);
$friendData = $stmt->fetch();
$friendshipStatus = $friendData['status'] ?? "NOT_FRIENDS"; // "NOT_FRIENDS" par défaut si non trouvé
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile de <?php echo htmlspecialchars($profile['username']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .profile-info {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-info img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }

        .profile-info p {
            margin: 10px 0;
            font-size: 16px;
        }

        .profile-info p strong {
            font-weight: bold;
        }

        form {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        button {
            padding: 8px 16px;
            background-color: #1877f2;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #165dbb;
        }

        @media screen and (max-width: 768px) {
            .profile-info {
                margin: 10px;
                padding: 10px;
            }

            .profile-info img {
                width: 100px;
                height: 100px;
            }

            .profile-info p {
                font-size: 14px;
            }
        }
    </style>

</head>

<body>

    <div class="profile-info">
        <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile Picture">
        <p><strong>@</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
        <p><strong>Anniversaire:</strong> <?php echo htmlspecialchars($profile['birthdate']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($profile['bio']); ?></p>
    </div>
    
    <?php
    if (in_array($friendshipStatus, ["NOT_FRIENDS", "DELETED"]) && $user_id != $profile_user_id) :
    ?>
        <form action="status_friend.php" method="post">
            <input type="hidden" name="friend_id" value="<?php echo $profile_user_id; ?>">
            <button type="submit">Ajouter comme ami</button>
        </form>
    <?php endif; ?>

</body>

</html>