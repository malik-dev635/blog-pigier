<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}



if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Vérifier si la catégorie existe et n'a pas d'articles
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM article_category WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            // Supprimer la catégorie
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = "La catégorie a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Impossible de supprimer une catégorie qui contient des articles.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Une erreur est survenue lors de la suppression de la catégorie.";
    }
}

header('Location: categories.php');
exit();
?> 