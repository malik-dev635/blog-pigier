<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion avec un message d'erreur
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer le terme de recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Récupérer tous les utilisateurs avec recherche
try {
    $sql = "SELECT * FROM users 
            WHERE username LIKE :search 
            OR email LIKE :search 
            OR role LIKE :search 
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search' => "%$search%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Utilisateurs | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #004494;
            color: #fff;
            padding: 1.5rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 1rem;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: #feca00;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #004494;
        }

        .btn-logout {
            background-color: #feca00;
            color: #004494;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #e0b200;
        }

        .users-table {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .users-table h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #004494;
            margin-bottom: 1.5rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background-color: #004494;
            color: #fff;
            font-weight: 600;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-edit {
            background-color: #feca00;
            color: #004494;
            border: none;
        }

        .btn-edit:hover {
            background-color: #e0b200;
        }

        .btn-delete {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-save {
            background-color: #28a745;
            color: #fff;
            border: none;
        }

        .btn-save:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                padding: 1rem;
            }

            .sidebar h2 {
                margin-bottom: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .btn-logout {
                padding: 0.5rem 1rem;
            }

            .users-table h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
        <a class="navbar-brand fw-bold" href="../index.php" style="color: #fff">
            <img src="../img/logo.png" alt="Logo" style="height: 40px" />
            blog
        </a>
            <h2>Dashboard</h2>
            <ul>
            <li><a href="dashboard.php">Articles</a></li>
                <li><a href="#">Catégories</a></li>
                <li><a href="#">Commentaires</a></li>
                <li><a href="users.php">Utilisateurs</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Gestion des Utilisateurs</h1>
                <a href="../auth/logout.php" class="btn btn-logout">Déconnexion</a>
            </div>

            <!-- Tableau des utilisateurs -->
            <div class="users-table">
                <h2>Liste des Utilisateurs</h2>
                <!-- Barre de recherche côté serveur -->
                <div class="mb-4">
                    <form action="users.php" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un utilisateur..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </form>
                </div>
                <!-- Barre de recherche côté client -->
                <div class="mb-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher en temps réel..." oninput="filterUsers()">
                </div>
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['id'] == 1): ?>
                                        <select name="role" class="form-select" disabled>
                                            <option value="admin" selected>Administrateur</option>
                                        </select>
                                    <?php else: ?>
                                        <form action="update_user_role.php" method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="role" class="form-select" onchange="this.form.submit()">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                                <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Éditeur</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
    <?php if ($user['id'] != 1): ?>
        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-edit btn-action">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-delete btn-action" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
            <i class="fas fa-trash"></i> Supprimer
        </a>
    <?php endif; ?>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filterUsers() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Commence à 1 pour ignorer l'en-tête
                const username = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const email = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const role = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();

                if (username.includes(input) || email.includes(input) || role.includes(input)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

