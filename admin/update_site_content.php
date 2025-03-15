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
    $content_key = $_POST['content_key'] ?? '';
    
    // Traitement de la citation de la semaine
    if ($content_key === 'weekly_quote') {
        $quote_text = trim($_POST['quote_text'] ?? '');
        $quote_author = trim($_POST['quote_author'] ?? '');
        
        // Validation des données
        if (empty($quote_text) || empty($quote_author)) {
            $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
            header('Location: ../index.php#quote-section');
            exit();
        }
        
        // Préparer les données à enregistrer
        $content_value = json_encode([
            'text' => $quote_text,
            'author' => $quote_author,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Vérifier si l'entrée existe déjà
            $check_query = "SELECT id FROM site_content WHERE content_key = ?";
            $stmt = $pdo->prepare($check_query);
            $stmt->execute([$content_key]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Mettre à jour l'entrée existante
                $update_query = "UPDATE site_content SET content_value = ?, updated_at = NOW() WHERE content_key = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([$content_value, $content_key]);
            } else {
                // Créer une nouvelle entrée
                $insert_query = "INSERT INTO site_content (content_key, content_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                $stmt = $pdo->prepare($insert_query);
                $stmt->execute([$content_key, $content_value]);
            }
            
            $_SESSION['success_message'] = "La citation a été mise à jour avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
        
        header('Location: ../index.php#quote-section');
        exit();
    }
    
    // Autres types de contenu peuvent être ajoutés ici
    
    // Si le type de contenu n'est pas reconnu
    $_SESSION['error_message'] = "Type de contenu non reconnu.";
    header('Location: ../index.php');
    exit();
}

// Si la méthode n'est pas POST, rediriger vers la page d'accueil
header('Location: ../index.php');
exit();
?> 