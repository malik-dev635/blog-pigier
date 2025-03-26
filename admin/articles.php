<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/loader.php';
session_start();


// Vérifier si l'utilisateur est connecté et a les droits admin ou editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}



// Récupérer les articles depuis la base de données
// Récupérer les articles avec leurs images
$query = "SELECT articles.id, articles.title, articles.image, users.username AS author, articles.created_at 
          FROM articles 
          JOIN users ON articles.author_id = users.id 
          ORDER BY articles.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Gestion des messages de succès
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

// Déconnexion de l'utilisateur
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
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
    <title>Dashboard | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../img/logo.png" />
    <link rel="shortcut icon" href="../img/logo.png" />

</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
       <?php include 'includes/sidebar.php'; ?>

       

        <!-- Main Content -->
        <div class="main-content">
            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-newspaper me-2"></i>Gestion des Articles</h1>
                <div class="header-actions">
                    <a href="add_article.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nouvel Article
                    </a>
                </div>
            </div>

            <!-- Success Message -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Articles Table -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Liste des Articles</h2>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchArticles" placeholder="Rechercher un article...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td>
                                        <img src="../<?php echo htmlspecialchars($article['image']); ?>" 
                                             alt="Image de l'article" class="article-img">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($article['title']); ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle me-2"></i>
                                            <?php echo htmlspecialchars($article['author']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-calendar me-2"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="edit_article.php?id=<?php echo $article['id']; ?>" 
                                               class="btn btn-action btn-edit" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_article.php?id=<?php echo $article['id']; ?>" 
                                               class="btn btn-action btn-delete" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');"
                                               title="Supprimer">
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
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchArticles').addEventListener('keyup', function() {
            let searchQuery = this.value.toLowerCase();
            let tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                let title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                let author = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (title.includes(searchQuery) || author.includes(searchQuery)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>

    <style>
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
</body>
</html>