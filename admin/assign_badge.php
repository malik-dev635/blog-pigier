<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin ou editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $badge_id = $_POST['badge_id'];

    if (!empty($user_id) && !empty($badge_id)) {
        try {
            // Vérifier si l'utilisateur a déjà ce badge
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_badges WHERE user_id = ? AND badge_id = ?");
            $stmt->execute([$user_id, $badge_id]);
            $exists = $stmt->fetchColumn();

            if (!$exists) {
                $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $badge_id]);
                
                $_SESSION['success_message'] = "Le badge a été attribué avec succès.";
            } else {
                $_SESSION['error_message'] = "L'utilisateur possède déjà ce badge.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'attribution du badge : " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Tous les champs sont requis.";
    }
}

header("Location: badges.php");
exit; 