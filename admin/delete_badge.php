<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin ou editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

if (isset($_GET['id'])) {
    $badge_id = $_GET['id'];

    try {
        // Supprimer d'abord les attributions de ce badge aux utilisateurs
        $stmt = $pdo->prepare("DELETE FROM user_badges WHERE badge_id = ?");
        $stmt->execute([$badge_id]);

        // Puis supprimer le badge lui-même
        $stmt = $pdo->prepare("DELETE FROM contributor_badges WHERE id = ?");
        $stmt->execute([$badge_id]);
        
        $_SESSION['success_message'] = "Le badge a été supprimé avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression du badge : " . $e->getMessage();
    }
}

header("Location: badges.php");
exit; 