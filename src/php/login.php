<?php
// Démarrer la session
session_start();

// JSON
header('Content-Type: application/json');

// Connexion BDD 
include 'config/dbconfig.php';

// Contenu qui va s'inserer en BDD
$username = $_POST['username'];

// Vérification que le nom d'utilisateur existe
$sql = "SELECT COUNT(*) FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$count = $stmt->fetchColumn();

// Réponse JSON en cas d'utilisateur inexistant
if ($count == 0) {
    echo json_encode(['error' => 'Ce nom d\'utilisateur n\'existe pas.']);
    exit();
}

// Stocker le nom d'utilisateur dans la session
$_SESSION['username'] = $username;

echo json_encode(['success' => 'Connexion reussie !']);
?>
