<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Vérifier si l'ID de l'événement est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['event_error'] = "ID d'événement invalide.";
    header('Location: manage_events.php');
    exit();
}

$event_id = (int)$_GET['id'];

// Supprimer l'événement
try {
    // Vérifier d'abord si l'événement existe
    $check_query = "SELECT id FROM events WHERE id = ?";
    $stmt = $pdo->prepare($check_query);
    $stmt->execute([$event_id]);
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['event_error'] = "Événement non trouvé.";
        header('Location: manage_events.php');
        exit();
    }
    
    // Supprimer l'événement
    $delete_query = "DELETE FROM events WHERE id = ?";
    $stmt = $pdo->prepare($delete_query);
    $stmt->execute([$event_id]);
    
    $_SESSION['event_success'] = "L'événement a été supprimé avec succès.";
} catch (PDOException $e) {
    $_SESSION['event_error'] = "Erreur lors de la suppression de l'événement : " . $e->getMessage();
}

// Rediriger vers la page de gestion des événements
header('Location: manage_events.php');
exit();
?> 