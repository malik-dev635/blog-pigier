<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];
    $uploadDir = 'img-profile/';
    $uploadFile = $uploadDir . basename($_FILES['profile_picture']['name']);

    // Vérifier si le dossier existe, sinon le créer
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Valider le fichier (taille, type, etc.)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé.']);
        exit;
    }

    // Déplacer le fichier uploadé
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
        // Mettre à jour la base de données
        try {
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id");
            $stmt->execute([
                ':profile_picture' => $uploadFile,
                ':user_id' => $user_id
            ]);

            // Mettre à jour la session
            $_SESSION['profile_picture'] = $uploadFile;

            echo json_encode(['success' => true, 'profile_picture' => $uploadFile]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload du fichier.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu.']);
}
?>