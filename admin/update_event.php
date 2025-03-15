<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // Validation des données
    $errors = [];
    
    if ($id <= 0) {
        $errors[] = "ID d'événement invalide.";
    }
    
    if (empty($title)) {
        $errors[] = "Le titre est obligatoire.";
    }
    
    if (empty($event_date)) {
        $errors[] = "La date est obligatoire.";
    }
    
    if (empty($time)) {
        $errors[] = "L'heure est obligatoire.";
    }
    
    if (empty($location)) {
        $errors[] = "Le lieu est obligatoire.";
    }
    
    if (empty($description)) {
        $errors[] = "La description est obligatoire.";
    }
    
    // Si pas d'erreurs, mettre à jour l'événement
    if (empty($errors)) {
        try {
            // Mettre à jour l'événement
            $query = "UPDATE events 
                      SET title = ?, event_date = ?, time = ?, location = ?, description = ?, updated_at = NOW() 
                      WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([$title, $event_date, $time, $location, $description, $id]);
            
            if ($result) {
                $_SESSION['event_success'] = "L'événement a été mis à jour avec succès.";
            } else {
                $_SESSION['event_error'] = "Erreur lors de la mise à jour de l'événement.";
            }
        } catch (PDOException $e) {
            $_SESSION['event_error'] = "Erreur lors de la mise à jour de l'événement : " . $e->getMessage();
        }
    } else {
        // S'il y a des erreurs, les stocker pour affichage
        $_SESSION['event_error'] = implode("<br>", $errors);
    }
}

// Rediriger vers la page de gestion des événements
header('Location: manage_events.php');
exit();
?> 