<?php
require_once __DIR__ . '/config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        $_SESSION['profile_picture'] = $target_file;
    }

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
    $stmt->execute([$username, $email, $_SESSION['profile_picture'], $user_id]);
    
    $_SESSION['success_message'] = "Profil mis à jour avec succès.";
    header("Location: profile.php");
    exit;
}

$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Profil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center">Modifier mon profil</h3>
            <form action="edit-profile.php" method="post" enctype="multipart/form-data">
                <div class="text-center position-relative">
                    <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'img/default-profile.png'); ?>" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    <label for="profile_picture" class="position-absolute top-50 start-50 translate-middle">
                        <i class="fas fa-pencil-alt text-white bg-dark p-2 rounded-circle" style="cursor: pointer;"></i>
                    </label>
                    <input type="file" name="profile_picture" id="profile_picture" class="d-none">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
            </form>
            <a href="profile.php" class="btn btn-secondary mt-3">Annuler</a>
        </div>
    </div>

    <script>
        document.getElementById('profile_picture').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('img.rounded-circle').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
