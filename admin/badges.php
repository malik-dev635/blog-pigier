<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/loader.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits admin ou editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer les badges depuis la base de données
$query = "SELECT * FROM contributor_badges ORDER BY name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les utilisateurs avec leurs badges
$users_query = "
    SELECT 
        u.id,
        u.username,
        u.profile_picture,
        GROUP_CONCAT(DISTINCT CONCAT(cb.name, ':', cb.color) ORDER BY cb.name) as badges,
        COUNT(DISTINCT a.id) as article_count
    FROM users u
    LEFT JOIN articles a ON u.id = a.author_id
    LEFT JOIN user_badges ub ON u.id = ub.user_id
    LEFT JOIN contributor_badges cb ON ub.badge_id = cb.id
    GROUP BY u.id
    ORDER BY u.username ASC";
$stmt = $pdo->prepare($users_query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Badges | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../img/logo.png" />
    <style>
        .badge-preview {
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
            font-size: 0.875rem;
        }
        .user-badge {
            margin: 0 3px;
            font-size: 0.8rem;
            color: #000;
        }
        .user-badge.light-text {
            color: #fff;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            margin: 0 0.2rem;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #000;
        }
        .btn-assign {
            background-color: #0d6efd;
            color: #fff;
        }
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
        }
        .dashboard-content {
            display: flex;
            gap: 2rem;
            padding: 2rem;
        }
        .content-section {
            flex: 1;
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .content-section h2 {
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.5rem;
        }
        .btn-assign {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-assign:hover {
            background: linear-gradient(45deg, #1976D2, #1565C0);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-assign i {
            font-size: 0.9rem;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .table tbody {
            display: block;
            max-height: calc(10 * 42px); /* 10 lignes de 42px de hauteur */
            overflow-y: auto;
        }
        
        .table thead, 
        .table tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        .table thead {
            width: calc(100% - 17px); /* Ajustement pour la barre de défilement */
        }
        
        /* Style amélioré pour l'input de couleur */
        input[type="color"] {
            -webkit-appearance: none;
            width: 100%;
            height: 40px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }
        
        input[type="color"]::-webkit-color-swatch {
            border: none;
            border-radius: 5px;
        }
        
        .color-preview-container {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .color-preview-container label {
            margin-right: 10px;
            min-width: 80px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-award me-2"></i>Gestion des Badges</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBadgeModal">
                        <i class="fas fa-plus"></i>
                        Nouveau Badge
                    </button>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Conteneur pour les tableaux côte à côte -->
            <div class="d-flex gap-4">
                <!-- Liste des badges -->
                <div class="card" style="flex: 0.7;">
                    <div class="card-header">
                        <h2 class="card-title">Liste des Badges</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Badge</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($badges as $badge): ?>
                                <tr>
                                    <td>
                                        <span class="badge-preview" style="background-color: <?= htmlspecialchars($badge['color']) ?>">
                                            <?= htmlspecialchars($badge['name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editBadgeModal" 
                                                    data-id="<?= $badge['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($badge['name']) ?>" 
                                                    data-color="<?= htmlspecialchars($badge['color']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="delete_badge.php?id=<?= $badge['id'] ?>" class="btn btn-action btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce badge ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="card" style="flex: 1.3;">
                    <div class="card-header">
                        <h2 class="card-title">Utilisateurs et leurs badges</h2>
                        <div class="mt-2">
                            <input type="text" id="searchUser" class="form-control" placeholder="Rechercher un utilisateur...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Badges</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <?php foreach ($users as $user): ?>
                                <tr class="user-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle me-2"></i>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($user['badges'])) {
                                            $badges_array = explode(',', $user['badges']);
                                            foreach ($badges_array as $badge_str) {
                                                list($name, $color) = explode(':', $badge_str);
                                        ?>
                                            <span class="badge user-badge" style="background-color: <?= htmlspecialchars($color) ?>">
                                                <?= htmlspecialchars($name) ?>
                                            </span>
                                        <?php 
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-action btn-assign" data-bs-toggle="modal" data-bs-target="#assignBadgeModal" 
                                                data-user-id="<?= $user['id'] ?>" 
                                                data-username="<?= htmlspecialchars($user['username']) ?>">
                                            <i class="fas fa-plus"></i> Assigner
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Badge -->
    <div class="modal fade" id="addBadgeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Badge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_badge.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="badgeName" class="form-label">Nom du badge</label>
                            <input type="text" class="form-control" id="badgeName" name="name" required>
                        </div>
                        <div class="color-preview-container">
                            <label for="badgeColor" class="form-label">Couleur</label>
                            <input type="color" class="form-control" id="badgeColor" name="color" value="#3498db" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Aperçu</label>
                            <div class="badge-preview" id="addBadgePreview" style="background-color: #3498db; color: #ffffff;">Aperçu du badge</div>
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

    <!-- Modal Modifier Badge -->
    <div class="modal fade" id="editBadgeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le Badge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit_badge.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editBadgeId" name="id">
                        <div class="mb-3">
                            <label for="editBadgeName" class="form-label">Nom du badge</label>
                            <input type="text" class="form-control" id="editBadgeName" name="name" required>
                        </div>
                        <div class="color-preview-container">
                            <label for="editBadgeColor" class="form-label">Couleur</label>
                            <input type="color" class="form-control" id="editBadgeColor" name="color" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Aperçu</label>
                            <div class="badge-preview" id="editBadgePreview">Aperçu du badge</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Attribuer Badge -->
    <div class="modal fade" id="assignBadgeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attribuer un Badge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="assign_badge.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="assignUserId" name="user_id">
                        <div class="mb-3">
                            <label class="form-label">Utilisateur</label>
                            <p id="assignUsername" class="form-control-static"></p>
                        </div>
                        <div class="mb-3">
                            <label for="assignBadgeId" class="form-label">Badge</label>
                            <select class="form-select" id="assignBadgeId" name="badge_id" required>
                                <?php foreach ($badges as $badge): ?>
                                <option value="<?= $badge['id'] ?>"><?= htmlspecialchars($badge['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Attribuer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Fonction pour mettre à jour l'aperçu du badge
        function updateBadgePreview(nameInput, colorInput, previewElement) {
            $(nameInput).on('input', function() {
                $(previewElement).text($(this).val() || 'Aperçu du badge');
            });
            
            $(colorInput).on('input', function() {
                $(previewElement).css('background-color', $(this).val());
                // Ajuster la couleur du texte en fonction de la luminosité
                const color = $(this).val();
                const r = parseInt(color.substr(1,2), 16);
                const g = parseInt(color.substr(3,2), 16);
                const b = parseInt(color.substr(5,2), 16);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                $(previewElement).css('color', luminance > 0.5 ? '#000000' : '#ffffff');
            });
        }

        // Initialiser les aperçus pour le modal d'ajout
        updateBadgePreview('#badgeName', '#badgeColor', '#addBadgePreview');
        
        // Initialiser les aperçus pour le modal de modification
        updateBadgePreview('#editBadgeName', '#editBadgeColor', '#editBadgePreview');

        // Gérer le modal de modification
        $('#editBadgeModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const name = button.data('name');
            const color = button.data('color');
            
            const modal = $(this);
            modal.find('#editBadgeId').val(id);
            modal.find('#editBadgeName').val(name);
            modal.find('#editBadgeColor').val(color);
            
            // Mettre à jour l'aperçu
            const preview = modal.find('#editBadgePreview');
            preview.text(name);
            preview.css('background-color', color);
            
            // Ajuster la couleur du texte
            const r = parseInt(color.substr(1,2), 16);
            const g = parseInt(color.substr(3,2), 16);
            const b = parseInt(color.substr(5,2), 16);
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            preview.css('color', luminance > 0.5 ? '#000000' : '#ffffff');
        });

        // Gérer le modal d'attribution
        $('#assignBadgeModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const userId = button.data('user-id');
            const username = button.data('username');
            
            const modal = $(this);
            modal.find('#assignUserId').val(userId);
            modal.find('#assignUsername').text(username);
        });
        
        // Recherche instantanée pour les utilisateurs
        $('#searchUser').on('input', function() {
            const searchText = $(this).val().toLowerCase();
            
            $('.user-row').each(function() {
                const username = $(this).find('td:first').text().toLowerCase();
                if (username.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
</body>
</html> 