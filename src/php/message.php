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


// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $content = $_POST['content'];

    $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $receiver_id, $content]);

    header('Location: private_messages.php');
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
        echo "Vous ne pouvez pas supprimer ce message.";
    }
} else {
    echo "Aucun message à supprimer.";
}


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
        <label for="receiver_id">ID du destinataire :</label>
        <input type="number" id="receiver_id" name="receiver_id" required>

        <label for="content">Message :</label>
        <textarea id="content" name="content" required></textarea>

        <button type="submit">Envoyer</button>
    </form>

    <h2>Mes messages</h2>
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