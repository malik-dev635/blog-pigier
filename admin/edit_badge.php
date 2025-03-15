<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/loader.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php?error=access_denied');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $color = trim(filter_input(INPUT_POST, 'color', FILTER_SANITIZE_STRING));

    if (!$id || empty($name) || empty($color)) {
        $_SESSION['error'] = "Tous les champs sont requis.";
        header('Location: badges.php');
        exit;
    }

    try {
        // Mettre à jour le badge dans la base de données
        $query = "UPDATE contributor_badges SET name = ?, color = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$name, $color, $id]);

        $_SESSION['success'] = "Le badge a été modifié avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Une erreur est survenue lors de la modification du badge.";
        error_log($e->getMessage());
    }

    header('Location: badges.php');
    exit;
} else {
    header('Location: badges.php');
    exit;
}
?> 