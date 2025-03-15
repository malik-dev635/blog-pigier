<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin ou editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $color = trim($_POST['color']);

    if (!empty($name) && !empty($color)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contributor_badges (name, color) VALUES (?, ?)");
            $stmt->execute([$name, $color]);
            
            $_SESSION['success_message'] = "Le badge a été créé avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la création du badge : " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Tous les champs sont requis.";
    }
}

header("Location: badges.php");
exit; 