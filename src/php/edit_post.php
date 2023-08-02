<?php
session_start();
require 'dbconfig.php';

// Récupérer l'ID de l'utilisateur à partir du nom d'utilisateur
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

// Maintenant $user['id'] contient l'ID de l'utilisateur
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['id'];
    $content = $_POST['content'];

    $sql = "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$content, $post_id, $user_id]);

    header('Location: dashboard.php');
    exit();
} else {
    $post_id = $_GET['id'];
}

$sql = "SELECT content FROM posts WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Mettez ici vos balises head -->
</head>
<body>
    <h1>Modifier le post</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $post_id ?>">
        <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
