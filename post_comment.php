<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = $_POST['article_id'];
    $comment = trim($_POST['comment']);
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    // Récupérer les informations de l'utilisateur
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error_message'] = "Utilisateur introuvable.";
            header("Location: article.php?id=" . $article_id);
            exit;
        }

        // Insérer le commentaire dans la base de données
        $stmt = $pdo->prepare("INSERT INTO comments (article_id, name, email, comment, created_at, parent_id) 
                               VALUES (:article_id, :name, :email, :comment, NOW(), :parent_id)");
        $stmt->execute([
            ':article_id' => $article_id,
            ':name' => $user['username'],
            ':email' => $user['email'],
            ':comment' => $comment,
            ':parent_id' => $parent_id
        ]);

        $_SESSION['success_message'] = "Commentaire posté avec succès !";
        header("Location: article.php?id=" . $article_id);
        exit;
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
?>