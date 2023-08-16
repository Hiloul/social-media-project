<?php
// Démarrer la session
session_start();

// Définir le type de contenu en JSON
header('Content-Type: application/json');

// S'assurer que le nom d'utilisateur et le mot de passe sont bien envoyés
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode(['error' => 'Nom d\'utilisateur et/ou mot de passe manquant(s).']);
    exit();
}

// Stocker les entrées de l'utilisateur dans des variables
$username = trim($_POST['username']);
$password = $_POST['password'];

// Connexion BDD 
require 'dbconfig.php';

// Vérifier que le nom d'utilisateur existe dans la base de données
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch();

// Vérifier que le nom d'utilisateur et le mot de passe correspondent
if ($user && password_verify($password, $user['password'])) {
    
    // Stocker le nom d'utilisateur dans la session
    $_SESSION['username'] = $username;

    // Mettre à jour le statut de connexion dans la base de données
    $sqlUpdate = "UPDATE users SET is_connected = 1 WHERE username = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$username]);

    echo json_encode(['success' => 'Connexion réussie ! (redirection en cours...)']);
} else {
    echo json_encode(['error' => 'Nom d\'utilisateur ou mot de passe incorrect.']);
}
?>
