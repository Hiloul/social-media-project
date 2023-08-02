<?php
$host = 'localhost';
$db   = 'socialmedia';
$user = 'root';
$pass = 'root';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);

?>
<!-- Pour le lier dans le projet
include 'config/dbconfig.php';
Utilisez $pdo pour exécuter des requêtes SQL -->
<!-- Ex de requête -->
<!-- $query = $pdo->query("SELECT * FROM table");
$results = $query->fetchAll(); -->

