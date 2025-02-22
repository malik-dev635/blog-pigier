<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer l'ID de l'article à supprimer
$article_id = $_GET['id'] ?? null;

if (!$article_id) {
    die("ID de l'article non spécifié.");
}

// Récupérer les informations de l'article pour supprimer l'image associée
$stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article non trouvé.");
}

// Définir le répertoire des uploads
$uploadDir = __DIR__ . '/../uploads/';

// Supprimer l'image associée si elle existe
if (!empty($article['image'])) {
    $imagePath = $uploadDir . basename($article['image']); // S'assurer que c'est un chemin complet

    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Supprimer les associations de catégories de l'article
$delete_categories_stmt = $pdo->prepare("DELETE FROM article_category WHERE article_id = ?");
$delete_categories_stmt->execute([$article_id]);

// Supprimer l'article de la base de données
$delete_article_stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$delete_article_stmt->execute([$article_id]);

// Rediriger vers la liste des articles ou une autre page
header("Location: dashboard.php");
exit;
?>
