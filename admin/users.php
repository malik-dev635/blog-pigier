<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/loader.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer le terme de recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Requête pour récupérer les utilisateurs avec le nombre d'articles
$users_query = "
    SELECT 
        u.id,
        u.username,
        u.email,
        u.role,
        u.profile_picture,
        u.created_at,
        COUNT(DISTINCT a.id) as article_count
    FROM users u
    LEFT JOIN articles a ON u.id = a.author_id
    WHERE u.username LIKE :search OR u.email LIKE :search
    GROUP BY u.id
    ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($users_query);
$stmt->execute(['search' => "%$search%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Utilisateurs | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../img/logo.png" />
    <style>
        .user-badge {
            margin: 0 3px;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            margin: 0 0.2rem;
        }
        .btn-edit {
            color: #000;
            border: 1px solid #ffc107;
            background-color: #ffc107;
        }
        .btn-edit:hover {
            background-color: #ffca2c;
            color: #000;
        }
        .btn-delete {
            color: #fff;
            border: 1px solid #dc3545;
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #bb2d3b;
            color: #fff;
        }
        .header .btn-primary {
            background-color: #020268;
            border-color: #020268;
        }
        .header .btn-primary:hover {
            background-color: #010144;
            border-color: #010144;
        }
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
        .profile-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px 0;
        }
        .submenu {
            list-style: none;
            padding-left: 20px;
            margin: 0;
        }
        .submenu li a {
            padding: 8px 15px;
            display: block;
            color: #fff;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s;
        }
        .submenu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        .submenu-toggle {
            cursor: pointer;
        }
        .submenu-toggle .fa-chevron-down {
            transition: transform 0.3s;
            font-size: 0.8em;
        }
        .submenu-toggle[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
        .sidebar-nav > li > a.submenu-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-users me-2"></i>Gestion des Utilisateurs</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus"></i>
                        Nouvel Utilisateur
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="search-bar mb-4">
                <form action="" method="GET" class="d-flex">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Rechercher un utilisateur..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary ms-2">
                        <i class="fas fa-search"></i>
                    </button>
                    </form>
                </div>

            <!-- Users List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Liste des Utilisateurs</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                    <thead>
                        <tr>
                                <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                                <th>Articles</th>
                                <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../<?php echo htmlspecialchars($user['profile_picture'] ?? 'img/default-avatar.png'); ?>" 
                                                 alt="Avatar" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px;">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $user['role'] === 'admin' ? 'bg-danger' : 
                                                ($user['role'] === 'editor' ? 'bg-warning text-dark' : 'bg-info'); 
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                </td>
                                    <td><?php echo $user['article_count']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" 
                                                    class="btn btn-action btn-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal"
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                    data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                                    data-profile-picture="<?php echo htmlspecialchars($user['profile_picture']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                                                   class="btn btn-action btn-delete"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                    <i class="fas fa-trash"></i>
        </a>
    <?php endif; ?>
                                        </div>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="add_user.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">Utilisateur</option>
                                <option value="editor">Éditeur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <img id="picturePreview" src="../img/default-avatar.png" class="profile-preview" alt="Aperçu">
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_user.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" class="form-control" id="editPassword" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Rôle</label>
                            <select class="form-select" id="editRole" name="role" required>
                                <option value="user">Utilisateur</option>
                                <option value="editor">Éditeur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editProfilePicture" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="editProfilePicture" name="profile_picture" accept="image/*">
                            <img id="editPicturePreview" src="../img/default-avatar.png" class="profile-preview" alt="Aperçu">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prévisualisation de l'image pour l'ajout d'utilisateur
            document.getElementById('profile_picture').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('picturePreview').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Prévisualisation de l'image pour l'édition d'utilisateur
            document.getElementById('editProfilePicture').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('editPicturePreview').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Initialisation des modals Bootstrap
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));

            // Gestion du modal d'édition
            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const username = this.getAttribute('data-username');
                    const email = this.getAttribute('data-email');
                    const role = this.getAttribute('data-role');
                    const profilePicture = this.getAttribute('data-profile-picture');

                    document.getElementById('editUserId').value = userId;
                    document.getElementById('editUsername').value = username;
                    document.getElementById('editEmail').value = email;
                    document.getElementById('editRole').value = role;
                    document.getElementById('editPicturePreview').src = '../' + (profilePicture || 'img/default-avatar.png');

                    editModal.show();
                });
            });
        });
    </script>
</body>
</html>

