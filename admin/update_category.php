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
    // Récupérer et valider les données
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Vérifier que l'ID est valide
    if (!$id) {
        $_SESSION['error_message'] = "ID de catégorie invalide.";
        header('Location: categories.php');
        exit;
    }

    // Vérifier que le nom n'est pas vide
    if (empty($name)) {
        $_SESSION['error_message'] = "Le nom de la catégorie est requis.";
        header('Location: categories.php');
        exit;
    }

    try {
        // Vérifier si le nom existe déjà pour une autre catégorie
        $check_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $check_stmt->execute([$name, $id]);
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Une catégorie avec ce nom existe déjà.";
            header('Location: categories.php');
            exit;
        }

        // Mettre à jour la catégorie
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);

        // Message de succès
        $_SESSION['success_message'] = "La catégorie a été mise à jour avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour de la catégorie : " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Méthode non autorisée.";
}

// Rediriger vers la page des catégories
header('Location: categories.php');
exit;
?> 