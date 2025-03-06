<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion avec un message d'erreur
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Empêcher la suppression de l'administrateur principal (id = 1)
    if ($user_id == 1) {
        $_SESSION['error_message'] = "Vous ne pouvez pas supprimer l'administrateur principal.";
        header("Location: users.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $user_id]);

        $_SESSION['success_message'] = "Utilisateur supprimé avec succès !";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
    }

    header("Location: users.php");
    exit;
}
