<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "socialmedia";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
  die("La connexion a échoué: " . $conn->connect_error);
}
?>


<!-- Pour lier dans le projet -->
<!-- include 'config/dbconfig.php';
$conn pour exécuter des requêtes SQL -->
