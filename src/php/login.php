<?php
// Démarrer la session
session_start();

// JSON
header('Content-Type: application/json');

// Connexion BDD 
include 'config/dbconfig.php';

// Contenu qui va s'insérer en BDD
$username = $_POST['username'];
$password = $_POST['password'];

// Vérification que le nom d'utilisateur et le mot de passe existent
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch();

// Réponse JSON en cas d'utilisateur inexistant ou mot de passe incorrect
if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['error' => 'Nom d\'utilisateur ou mot de passe incorrect.']);
    exit();
}

// Stocker le nom d'utilisateur dans la session
$_SESSION['username'] = $username;

echo json_encode(['success' => 'Connexion réussie ! (redirection en cours...)']);
?>



