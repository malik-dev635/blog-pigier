<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Vérifier si un ID de commentaire est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de commentaire invalide.";
    header("Location: comments.php");
    exit;
}

$comment_id = (int)$_GET['id'];

try {
    // Vérifier si le commentaire existe
    $check_query = "SELECT id FROM comments WHERE id = ?";
    $stmt = $pdo->prepare($check_query);
    $stmt->execute([$comment_id]);
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error_message'] = "Le commentaire n'existe pas.";
        header("Location: comments.php");
        exit;
    }

    // Supprimer le commentaire
    $delete_query = "DELETE FROM comments WHERE id = ?";
    $stmt = $pdo->prepare($delete_query);
    $stmt->execute([$comment_id]);

    $_SESSION['success_message'] = "Le commentaire a été supprimé avec succès.";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression du commentaire.";
    error_log("Erreur de suppression de commentaire : " . $e->getMessage());
}

// Rediriger vers la page des commentaires
header("Location: comments.php");
exit;
?>