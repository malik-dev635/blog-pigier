<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $title = trim($_POST['title'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Le titre est obligatoire.";
    }
    
    if (empty($event_date)) {
        $errors[] = "La date est obligatoire.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        $errors[] = "Le format de la date doit être YYYY-MM-DD.";
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
    
    // Si pas d'erreurs, insérer l'événement
    if (empty($errors)) {
        try {
            // Vérifier si la table existe, sinon la créer
            $pdo->exec("CREATE TABLE IF NOT EXISTS events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                event_date DATE NOT NULL,
                time VARCHAR(50) NOT NULL,
                location VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT NULL
            )");
            
            $query = "INSERT INTO events (title, event_date, time, location, description, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$title, $event_date, $time, $location, $description]);
            
            $_SESSION['event_success'] = "L'événement a été ajouté avec succès.";
            header('Location: manage_events.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout de l'événement : " . $e->getMessage();
        }
    }
    
    // S'il y a des erreurs, les stocker pour affichage
    if (!empty($errors)) {
        $_SESSION['event_error'] = implode("<br>", $errors);
        header('Location: manage_events.php');
        exit();
    }
}

// Si la méthode n'est pas POST, rediriger vers la page de gestion des événements
header('Location: manage_events.php');
exit();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ajouter un Événement | Blog Pigier</title>
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
                        <h2 class="user-name"><?php echo htmlspecialchars($current_user['username']); ?></h2>
                        <span class="user-role"><?php echo ucfirst(htmlspecialchars($current_user['role'])); ?></span>
                    </div>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="articles.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Articles</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="comments.php">
                        <i class="fas fa-comments"></i>
                        <span>Commentaires</span>
                    </a>
                </li>
                <li>
                    <a href="badges.php">
                        <i class="fas fa-award"></i>
                        <span>Badges</span>
                    </a>
                </li>
                <li>
                    <a href="manage_events.php" class="active">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Événements</span>
                    </a>
                </li>
                <li>
                    <a href="../index.php">
                        <i class="fas fa-eye"></i>
                        <span>Voir le site</span>
                    </a>
                </li>
                <li>
                    <a href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-plus-circle me-2"></i>Nouvel Événement</h1>
                <div class="header-actions">
                    <a href="manage_events.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout d'événement -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informations de l'événement</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titre de l'événement</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="invalid-feedback">
                                        Veuillez saisir un titre pour l'événement.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                                    <div class="invalid-feedback">
                                        Veuillez sélectionner une date.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="time" class="form-label">Heure</label>
                                    <input type="text" class="form-control" id="time" name="time" placeholder="Ex: 14:00 - 17:00" required>
                                    <div class="invalid-feedback">
                                        Veuillez saisir l'heure de l'événement.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lieu</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                    <div class="invalid-feedback">
                                        Veuillez saisir le lieu de l'événement.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                    <div class="invalid-feedback">
                                        Veuillez saisir une description pour l'événement.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du formulaire
        (function () {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html> 