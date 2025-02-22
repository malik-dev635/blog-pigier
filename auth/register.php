<?php
require '../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $errors = [];

    // Vérifier si tous les champs sont remplis
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Tous les champs sont requis.";
    } 
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    // Vérification de la longueur du mot de passe
    if (strlen($password) < 5) {
        $errors[] = "Le mot de passe doit contenir au moins 5 caractères.";
    }

    // Vérification de la correspondance des mots de passe
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        // Vérifier si l'utilisateur ou l'email existent déjà
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            if ($existingUser['username'] === $username) {
                $errors[] = "Ce nom d'utilisateur est déjà utilisé.";
            }
            if ($existingUser['email'] === $email) {
                $errors[] = "Cet email est déjà enregistré.";
            }
        } else {
            // Hashage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur dans la base de données
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $success = $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword]);

            if ($success) {
                $_SESSION['success_message'] = "Inscription réussie ! Connectez-vous.";
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/auth.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <title>Inscription</title>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <a class="navbar-brand fw-bold" href="../index.php" style="color: #020268">
                <img src="../img/logo.png" alt="Logo" style="height: 40px" />
                blog
            </a>
            <h1 class="text-center mb-4">Inscription</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
            </form>

            <div class="text-center mt-3">
                <p>Déjà un compte ? <a href="login.php" class="text-primary">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</body>
</html>
