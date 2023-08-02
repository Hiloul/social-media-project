<?php
// JSON
header('Content-Type: application/json');

// Connexion BDD 
include 'config/dbconfig.php';

// Contenu qui va s'inserer en BDD
$username = $_POST['username'];

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
// Créer l'utilisateur en BDD
$sql = "INSERT INTO users (username) VALUES (?)";
$stmt= $pdo->prepare($sql);
$stmt->execute([$username]);

echo json_encode(['success' => 'Utilisateur créé avec succès.']);
