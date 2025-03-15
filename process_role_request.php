<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $requested_role = $_POST['requested_role'];
    $reason = $_POST['reason'];
    $experience = $_POST['experience'];
    $status = 'pending'; // pending, approved, rejected

    try {
        // Vérifier si une demande est déjà en cours
        $check_sql = "SELECT id FROM role_requests 
                     WHERE user_id = ? AND status = 'pending'";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$user_id]);
        
        if ($check_stmt->fetch()) {
            $_SESSION['error_message'] = "Vous avez déjà une demande en cours d'examen.";
            header('Location: index.php');
            exit;
        }

        // Insérer la nouvelle demande
        $sql = "INSERT INTO role_requests (user_id, requested_role, reason, experience, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $requested_role, $reason, $experience, $status]);

        $_SESSION['success_message'] = "Votre demande a été envoyée avec succès. Nous l'examinerons dans les plus brefs délais.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Une erreur est survenue lors de l'envoi de votre demande.";
    }
}

header('Location: index.php');
exit; 