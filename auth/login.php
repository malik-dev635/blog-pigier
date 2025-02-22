<?php
require '../config/config.php';
session_start();

$error_message = '';
$username = ''; // Pour pré-remplir le champ si erreur

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Vérifier si les champs sont vides
    if (empty($username) || empty($password)) {
        $error_message = "⚠️ Veuillez remplir tous les champs.";
    } else {
        // Préparer la requête SQL sécurisée
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Sécurisation de session

            // Stocker les informations de l'utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_picture'] = !empty($user['profile_picture']) ? $user['profile_picture'] : '../img-profile/default-avatar.avif';
            $_SESSION['success_message'] = "✅ Connexion réussie. Bienvenue, " . htmlspecialchars($user['username']) . " !";
            
            header("Location: ../index.php?login=success");
            exit;
        } else {
            $error_message = "❌ Identifiant ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="shortcut icon" href="../img/logo.png">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-lg p-4" style="width: 400px;">
        <a class="navbar-brand fw-bold" href="../index.php" style="color: #020268">
            <img src="../img/logo.png" alt="Logo" style="height: 40px">
            blog
        </a>
        
        <h1 class="text-center mb-4">Connexion</h1>

        <!-- Message d'erreur -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center" id="error-message">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form action="" method="POST" class="login-form">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur ou Email</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>

        <!-- Lien pour créer un compte -->
        <div class="text-center mt-3">
            <p>Pas encore de compte ? <a href="register.php" class="text-primary">Inscrivez-vous</a></p>
        </div>
    </div>
</div>

<script>
    // Masquer automatiquement les messages d'erreur après 5 secondes
    $(document).ready(function () {
        setTimeout(function () {
            $("#error-message").fadeOut("slow");
        }, 5000);
    });
</script>

</body>
</html>
