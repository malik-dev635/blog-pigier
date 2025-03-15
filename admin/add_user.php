<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/loader.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    try {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Un utilisateur avec ce nom ou cet email existe déjà.";
            header("Location: users.php");
            exit;
        }

        // Gérer l'upload de l'image de profil
        $profile_picture = 'img/default-avatar.png';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $fileName = uniqid() . '_' . basename($file['name']);
            $uploadDir = __DIR__ . '/../img-profile/';
            $uploadFile = $uploadDir . $fileName;

            // Vérifier le type de fichier
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Type de fichier non autorisé. Seuls les fichiers JPEG, PNG et GIF sont acceptés.");
            }

            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $profile_picture = 'img-profile/' . $fileName;
            }
        }

        // Hasher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insérer le nouvel utilisateur
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, profile_picture, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$username, $email, $hashed_password, $role, $profile_picture]);

        $_SESSION['success'] = "L'utilisateur a été créé avec succès.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Une erreur est survenue : " . $e->getMessage();
    }
}

header("Location: users.php");
exit; 