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

// Récupérer tous les événements
try {
    $events_query = "SELECT * FROM events ORDER BY event_date ASC";
    $stmt = $pdo->prepare($events_query);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la table n'existe pas, créer la table
    if ($e->getCode() == '42S02') {
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
        $events = [];
    } else {
        $_SESSION['event_error'] = "Erreur lors de la récupération des événements : " . $e->getMessage();
        header('Location: ../index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Événements | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../img/logo.png" />
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-calendar-alt me-2"></i>Gestion des Événements</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus"></i>
                        Nouvel Événement
                    </button>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['event_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                        echo $_SESSION['event_success']; 
                        unset($_SESSION['event_success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['event_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                        echo $_SESSION['event_error']; 
                        unset($_SESSION['event_error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Liste des événements -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Liste des Événements</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun événement n'a été ajouté. Utilisez le bouton "Nouvel Événement" pour en créer un.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Titre</th>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Lieu</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['title']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($event['event_date'])) ?></td>
                                            <td><?= htmlspecialchars($event['time']) ?></td>
                                            <td><?= htmlspecialchars($event['location']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning edit-event-btn" 
                                                        data-id="<?= $event['id'] ?>"
                                                        data-title="<?= htmlspecialchars($event['title']) ?>"
                                                        data-date="<?= htmlspecialchars($event['event_date']) ?>"
                                                        data-time="<?= htmlspecialchars($event['time']) ?>"
                                                        data-location="<?= htmlspecialchars($event['location']) ?>"
                                                        data-description="<?= htmlspecialchars($event['description']) ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editEventModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="delete_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Événement -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addEventModalLabel">Nouvel Événement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="add_event.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de l'événement</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Heure</label>
                            <input type="text" class="form-control" id="time" name="time" placeholder="Ex: 14:00 - 17:00" required>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Lieu</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Événement -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editEventModalLabel">Modifier l'Événement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update_event.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_event_id" name="id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Titre de l'événement</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit_event_date" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_time" class="form-label">Heure</label>
                            <input type="text" class="form-control" id="edit_time" name="time" placeholder="Ex: 14:00 - 17:00" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Lieu</label>
                            <input type="text" class="form-control" id="edit_location" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Remplir le modal d'édition avec les données de l'événement
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-event-btn');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    const date = this.getAttribute('data-date');
                    const time = this.getAttribute('data-time');
                    const location = this.getAttribute('data-location');
                    const description = this.getAttribute('data-description');
                    
                    console.log("Édition de l'événement:", {id, title, date, time, location, description});
                    
                    document.getElementById('edit_event_id').value = id;
                    document.getElementById('edit_title').value = title;
                    document.getElementById('edit_event_date').value = date;
                    document.getElementById('edit_time').value = time;
                    document.getElementById('edit_location').value = location;
                    document.getElementById('edit_description').value = description;
                });
            });
            
            // Vérifier le formulaire avant soumission
            document.querySelector('#editEventModal form').addEventListener('submit', function(e) {
                const id = document.getElementById('edit_event_id').value;
                const title = document.getElementById('edit_title').value;
                const date = document.getElementById('edit_event_date').value;
                const time = document.getElementById('edit_time').value;
                const location = document.getElementById('edit_location').value;
                const description = document.getElementById('edit_description').value;
                
                console.log("Soumission du formulaire:", {id, title, date, time, location, description});
                
                if (!id || !title || !date || !time || !location || !description) {
                    e.preventDefault();
                    alert("Veuillez remplir tous les champs obligatoires.");
                }
            });
        });
    </script>
</body>
</html> 