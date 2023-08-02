<?php
// Connexion BDD 
require 'dbconfig.php';

header('Content-Type: application/json'); // Assurez-vous que votre script renvoie du JSON

// Récupération des informations de l'utilisateur
$username = $_POST['username'];
$password = $_POST['password'];

// Création d'un nom d'utilisateur d'au - 8 caracters
if (strlen($username) < 8) {
    echo json_encode(['error' => 'Le nom d\'utilisateur doit contenir au moins 8 caractères.']);
    exit();
}

// Vérification que le nom d'utilisateur est bien libre
$sql = "SELECT COUNT(*) FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$count = $stmt->fetchColumn();

// Réponse JSON en cas d'utilisateur déja existant
if ($count > 0) {
    echo json_encode(['error' => 'Ce nom d\'utilisateur existe déjà.']);
    exit();
}

// Hachage du mot de passe
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Enregistrement de l'utilisateur dans la base de données
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password]);

    echo json_encode(['success' => 'Utilisateur enregistré avec succès! (redirection en cours...)']);
} catch (Exception $e) {
    // Si une erreur se produit, renvoyer une réponse d'erreur
    echo json_encode(['error' => $e->getMessage()]);
}
?>
