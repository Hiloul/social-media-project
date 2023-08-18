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

// Obtenir les informations du profil
$sql = "SELECT * FROM profils WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$profil = $stmt->fetch();

// Récupération des informations du profil
$sql = "SELECT id, user_id, profile_picture, bio, birthdate, created_at, updated_at FROM profils WHERE 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$profil = $stmt->fetch();

// Obtenir les amis de l'utilisateur
$sql = "SELECT users.* FROM friends 
        JOIN users ON friends.friend_id = users.id 
        WHERE friends.user_id = ? AND friends.status = 'ACCEPTED'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$friends = $stmt->fetchAll();

// Supprimer un ami
if (isset($_GET['delete_friend'])) {
    $friend_to_delete_id = filter_var($_GET['delete_friend'], FILTER_SANITIZE_NUMBER_INT);
    $pdo->beginTransaction();
    try {
        $sql = "SELECT * FROM friends WHERE user_id = ? AND friend_id = ? AND status != 'DELETED'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_to_delete_id]);
        
        if ($stmt->rowCount() == 0) {
            // L'utilisateur que vous essayez de supprimer n'est pas votre ami.
            $pdo->rollBack();
            header('Location: profil.php?error=not_friend');
            exit();
        }

        $sql = "UPDATE friends 
                SET status = 'DELETED'
                WHERE user_id = ? AND friend_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_to_delete_id]);

        if ($stmt->rowCount()) {
            // Statut amical mis à jour: 'DELETED'.
            $pdo->commit();
            header('Location: profil.php?success=friend_deleted');
            exit();
        } else {
            $pdo->rollBack();
            header('Location: profil.php?error=delete_failed');
            exit();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: profil.php?error=db_error');
        exit();
    }
} else {
    header('Location: profil.php?error=no_friend_id');
    exit();
}

// Bloquer un ami
if (isset($_POST['friend_id'])) {
    $friend_to_block_id = filter_var($_POST['friend_id'], FILTER_SANITIZE_NUMBER_INT);
    $pdo->beginTransaction();

    try {
        // Verifier que l'ami n'est pas déjà bloqué
        $sql = "SELECT * FROM friends WHERE user_id = ? AND friend_id = ? AND status != 'BLOCKED'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_to_block_id]);

        if ($stmt->rowCount() == 0) {
            // L'utilisateur que vous essayez de bloquer est soit déjà bloqué, soit pas votre ami.
            $pdo->rollBack();
            header('Location: profil.php?error=not_friend_or_blocked');
            exit();
        }

        $sql = "UPDATE friends 
                SET status = 'BLOCKED'
                WHERE user_id = ? AND friend_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_to_block_id]);

        if ($stmt->rowCount()) {
            // Statut amical mis à jour: 'BLOCKED'.
            $pdo->commit();
            header('Location: profil.php?success=friend_blocked');
            exit();
        } else {
            $pdo->rollBack();
            header('Location: profil.php?error=block_failed');
            exit();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: profil.php?error=db_error');
        exit();
    }
} else {
    header('Location: profil.php?error=no_friend_id');
    exit();
}
echo "Friend ID: " . $_POST['friend_id'];
echo "Rows count: " . $stmt->rowCount();



// Envoie une demande d'ami
if (isset($_POST['friend_id'])) {
    $friend_id = filter_var($_POST['friend_id'], FILTER_SANITIZE_NUMBER_INT);

    // Commencer la transaction
    $pdo->beginTransaction();

    try {
        // Vérifier si une demande d'ami a déjà été envoyée
        $sql = "SELECT * FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'PENDING'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_id]);

        if ($stmt->rowCount() > 0) {
            echo "Une demande d'ami a déjà été envoyée à cet utilisateur.";
            $pdo->rollBack();
            exit();
        }

        // Insérer une nouvelle demande d'ami avec un statut 'PENDING'
        $sql = "INSERT INTO friends (user_id, friend_id, status, created_at, updated_at) 
                VALUES (?, ?, 'PENDING', NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $friend_id]);

        if ($stmt->rowCount() > 0) {
            echo "Demande d'ami envoyée avec succès.";
            // Valider la transaction
            $pdo->commit();
        } else {
            echo "Il y a eu un problème lors de l'envoi de la demande d'ami.";
            // Annuler la transaction
            $pdo->rollBack();
        }
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        echo 'Erreur: ' . $e->getMessage();
    }
} else {
    echo "No friend_id provided.";
}
