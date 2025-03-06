-<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :user_id");
        $stmt->execute([':role' => $role, ':user_id' => $user_id]);

        $_SESSION['success_message'] = "Rôle de l'utilisateur mis à jour avec succès !";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du rôle : " . $e->getMessage();
    }

    header("Location: users.php");
    exit;
}
?>