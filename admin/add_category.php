<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Vérifier que le nom n'est pas vide
    if (empty($name)) {
        $_SESSION['error_message'] = "Le nom de la catégorie est requis.";
        header('Location: categories.php');
        exit;
    }

    try {
        // Vérifier si le nom existe déjà
        $check_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check_stmt->execute([$name]);
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Une catégorie avec ce nom existe déjà.";
            header('Location: categories.php');
            exit;
        }

        // Insérer la nouvelle catégorie
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);

        // Message de succès
        $_SESSION['success_message'] = "La catégorie a été ajoutée avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de l'ajout de la catégorie : " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Méthode non autorisée.";
}

// Rediriger vers la page des catégories
header('Location: categories.php');
exit;
?> 