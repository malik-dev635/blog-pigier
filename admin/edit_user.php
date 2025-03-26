<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer l'ID de l'utilisateur à modifier
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur introuvable.";
        header("Location: users.php");
        exit;
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    // Validation des données
    if (empty($username) || empty($email) || empty($role)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    } else {
        try {
            $profile_picture = $user['profile_picture']; // Garder l'ancienne image par défaut

            // Traitement de l'upload de l'image
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_picture']['name'];
                $tmp_name = $_FILES['profile_picture']['tmp_name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $new_filename = uniqid() . '.' . $ext;
                    $upload_path = '../uploads/profiles/' . $new_filename;
                    
                    // Créer le dossier s'il n'existe pas
                    if (!file_exists('../uploads/profiles/')) {
                        mkdir('../uploads/profiles/', 0777, true);
                    }

                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        // Supprimer l'ancienne image si elle existe
                        if ($user['profile_picture'] && file_exists('../' . $user['profile_picture'])) {
                            unlink('../' . $user['profile_picture']);
                        }
                        $profile_picture = 'uploads/profiles/' . $new_filename;
                    }
                }
            }

            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, role = :role, profile_picture = :profile_picture WHERE id = :user_id");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':profile_picture' => $profile_picture,
                ':user_id' => $user_id
            ]);

            $_SESSION['success_message'] = "Informations de l'utilisateur mises à jour avec succès !";
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Modifier l'utilisateur | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../img/logo.png" />
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="user-profile">
                    <img src="../<?php echo htmlspecialchars($current_user['profile_picture'] ?? 'img/default-avatar.png'); ?>" 
                         alt="Photo de profil" 
                         class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($current_user['username']); ?></span>
                        <span class="user-role"><?php echo ucfirst(htmlspecialchars($current_user['role'])); ?></span>
                    </div>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-newspaper"></i>
                        Articles
                    </a>
                </li>
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        Catégories
                    </a>
                </li>
                <li>
                    <a href="comments.php">
                        <i class="fas fa-comments"></i>
                        Commentaires
                    </a>
                </li>
                <li>
                    <a href="users.php" class="active">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                </li>
                <li>
                    <a href="../index.php">
                        <i class="fas fa-home"></i>
                        Voir le site
                    </a>
                </li>
                <li>
                    <a href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-user-edit me-2"></i>Modifier l'utilisateur</h1>
            </div>

            <!-- Edit User Form Card -->
            <div class="card">
                <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $_SESSION['error_message'] ?>
                        </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                    <form action="edit_user.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <div class="mb-4 text-center">
                            <img src="../<?= htmlspecialchars($user['profile_picture'] ?? 'img/default-avatar.png') ?>" 
                                 alt="Photo de profil actuelle" 
                                 class="rounded-circle mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">
                                    <i class="fas fa-camera me-2"></i>Changer la photo de profil
                                </label>
                                <input type="file" 
                                       name="profile_picture" 
                                       id="profile_picture" 
                                       class="form-control"
                                       accept="image/jpeg,image/png,image/gif">
                                <small class="text-muted">Formats acceptés : JPG, PNG, GIF</small>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-2"></i>Nom d'utilisateur
                            </label>
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($user['username']) ?>" 
                                   required>
                    </div>
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($user['email']) ?>" 
                                   required>
                    </div>
                        <div class="mb-4">
                            <label for="role" class="form-label">
                                <i class="fas fa-user-tag me-2"></i>Rôle
                            </label>
                            <select name="role" id="role" class="form-select" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                            <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Éditeur</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                    </div>
                        <div class="d-flex gap-2">
                            <a href="users.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prévisualisation de l'image
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.rounded-circle');
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>