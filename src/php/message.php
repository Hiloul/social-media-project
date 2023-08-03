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
// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_username = $_POST['receiver_username'];
    $content = $_POST['content'];

    // Récupérer l'ID du destinataire à partir du nom d'utilisateur
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$receiver_username]);
    $receiver = $stmt->fetch();

    if (!$receiver) {
        die('Destinataire inconnu.');
    }

    $receiver_id = $receiver['id'];

    // Insérer le nouveau message dans la base de données
    $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $receiver_id, $content]);

    header('Location: messages.php');
    exit();
}

if (isset($_GET['delete_message'])) {
    $message_id = $_GET['delete_message'];

    // Assurez-vous que le message appartient à l'utilisateur connecté
    $sql = "SELECT * FROM messages WHERE id = ? AND receiver_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$message_id, $user_id]);
    $message = $stmt->fetch();

    if ($message) {
        // Si le message appartient à l'utilisateur, le supprimer
        $sql = "DELETE FROM messages WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$message_id]);

        echo "Message supprimé avec succès.";
    } else {
        echo "Aucun message à supprimer.";
    }
}

// Récupérer les messages de l'utilisateur
$sql = "
    SELECT messages.*, users.username AS sender_username 
    FROM messages 
    JOIN users ON messages.sender_id = users.id 
    WHERE messages.receiver_id = ? 
    ORDER BY messages.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Messagerie privée</title>
</head>

<body>
    <h1>Messagerie privée</h1>
    <a href="profil.php">Retour au profil</a>
    <h2>Envoyer un message</h2>
    <form method="POST">
        <label for="receiver_username">Destinataire :</label>
        <input type="text" id="receiver_username" name="receiver_username" required>

        <label for="content">Message :</label>
        <textarea id="content" name="content" required></textarea>

        <button type="submit">Envoyer</button>
    </form>

    <h2>Message reçus</h2>
    <?php if (!empty($messages)) : ?>
        <?php foreach ($messages as $message) : ?>
            <div>
                <p>De : <?= htmlspecialchars($message['sender_username']) ?></p>
                <p>Message : <?= htmlspecialchars($message['content']) ?></p>
                <p>Reçu le : <?= date("d-m-Y H:i", strtotime($message['created_at'])) ?></p>
                <a href="message.php?delete_message=<?= $message['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</a>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Aucun messages reçus.</p>
    <?php endif; ?>


</body>

</html>
