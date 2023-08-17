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

// Mettre à jour le statut des messages de "NOT_RECEIVED" à "UNREAD" lorsque l'utilisateur est connecté
$sqlUpdate = "UPDATE messages SET status = 'UNREAD' WHERE receiver_id = ? AND status = 'NOT_RECEIVED'";
$stmtUpdate = $pdo->prepare($sqlUpdate);
$stmtUpdate->execute([$user_id]);

if (isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    // Mettre à jour le statut du message à "READ"
    $sql = "UPDATE messages SET status = 'READ' WHERE id = ? AND receiver_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$message_id, $user_id]);
}

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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $receiver_username = $_POST['receiver_username'];
        $content = $_POST['content'];
        // Insérer le nouveau message dans la base de données
        $sql = "INSERT INTO messages (sender_id, receiver_id, content, status) VALUES (?, ?, ?, 'NOT_RECEIVED')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $receiver_id, $content]);
    }
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


// Récupérer les messages envoyés par l'utilisateur
$sql = "
    SELECT messages.*, users.username AS receiver_username 
    FROM messages 
    JOIN users ON messages.receiver_id = users.id 
    WHERE messages.sender_id = ? 
    ORDER BY messages.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$sent_messages = $stmt->fetchAll();

if (isset($_GET['delete_message'])) {
    $message_id = $_GET['delete_message'];

    // Assurez-vous que le message appartient à l'utilisateur connecté (soit comme récepteur, soit comme expéditeur)
    $sql = "SELECT * FROM messages WHERE id = ? AND (receiver_id = ? OR sender_id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$message_id, $user_id, $user_id]);
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

?>

<!DOCTYPE html>
<html>

<head>
    <title>Messagerie privée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
        }

        h1,
        h2 {
            color: #4b4e4f;
        }

        a {
            color: #4267B2;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.1);
        }

        form input[type=text],
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccd0d5;
            border-radius: 5px;
        }

        form button {
            background-color: #4267B2;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #365899;
        }

        .message {
            background-color: #ffffff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.1);
        }

        /* CSS responsive */
        @media only screen and (max-width: 600px) {

            form input[type=text],
            form textarea {
                width: 100%;
            }
        }

        /* Style du scroll sur plusieru navigateurs */
        .messages-container {
            max-height: 400px;
            /* définissez la hauteur maximale en fonction de vos besoins */
            overflow-y: auto;
            /* défilement vertical lorsque le contenu dépasse la hauteur maximale */
            padding: 10px;
        }

        /* Personnalisation de la barre de défilement pour les navigateurs basés sur Chromium */
        .messages-container::-webkit-scrollbar {
            width: 12px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: #f0f2f5;
        }

        .messages-container::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 20px;
            border: 3px solid #f0f2f5;
        }

        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Personnalisation de la barre de défilement pour Firefox */
        .messages-container {
            scrollbar-width: thin;
            scrollbar-color: #888 #f0f2f5;
            scroll-behavior: smooth;
        }
    </style>
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

    <h2>Messages reçus</h2>
    <div class="messages-container">
        <?php if (!empty($messages)) : ?>
            <?php foreach ($messages as $message) : ?>
                <div class="message">
                    <p>De : <?= htmlspecialchars($message['sender_username']) ?></p>
                    <p>Message : <?= htmlspecialchars($message['content']) ?></p>
                    <p>Reçu le : <?= date("d-m-Y H:i", strtotime($message['created_at'])) ?></p>
                    <a href="#" data-message-id="<?= $message['id'] ?>" onclick="toggleMessage(this); return false;">Lire</a>

                    <a href="message.php?delete_message=<?= $message['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Aucun message reçu.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleMessage(element) {
            const messageId = element.getAttribute("data-message-id");

            // Appel AJAX pour mettre à jour le statut du message à "READ"
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'message.php?message_id=' + messageId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Vous pouvez ajouter du code ici pour faire quelque chose après avoir marqué le message comme lu.
                    // Par exemple, masquer le lien "Lire" ou changer le style du message.
                    element.style.display = "none"; // masquer le lien après avoir cliqué dessus
                }
            };
            xhr.send();
        }
    </script>

    <h2>Messages envoyés</h2>
    <div class="messages-container">
        <?php if (!empty($sent_messages)) : ?>
            <?php foreach ($sent_messages as $message) : ?>
                <div class="message">
                    <p>A: <?= htmlspecialchars($message['receiver_username']) ?></p>
                    <p>Message: <?= htmlspecialchars($message['content']) ?></p>
                    <p>Date: <?= date('d-m-Y H:i', strtotime($message['created_at'])) ?></p>
                    <a href="message.php?delete_message=<?= $message['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Aucun message envoyé.</p>
        <?php endif; ?>
    </div>

</body>

</html>