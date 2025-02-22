<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
   

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            
            ':user_id' => $user_id
        ]);

        // Mettre à jour les informations de session
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
       

        $_SESSION['success_message'] = "Profil mis à jour avec succès.";
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
?>