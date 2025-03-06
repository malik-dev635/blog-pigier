<?php
require_once __DIR__ . '/../config/config.php';
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Blog Pigier</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/style.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
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
   
</head>
<body>
    <style>

.articles-table {
  background: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.articles-table h2 {
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
  vertical-align: middle;
}

.table th {
  background-color: #004494;
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
}

.table tr:hover {
  background-color: #f8f9fa;
}

.article-img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #ddd;
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

.btn-addarticle {
  background-color: #004494;
  color: #fff;
  border: 2px #004494 solid;
  padding: 1rem 1.5rem;
  border-radius: 25px;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.btn-addarticle:hover {
  border: 2px #004494 solid;
  background-color: transparent;
}

.articles-table {
  padding: 1.5rem;
  border-radius: 10px;
}

.articles-table h2 {
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

  .articles-table h2 {
    font-size: 1.5rem;
  }
}

    </style>
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
           
            <!-- Header -->
<div class="header">
    <h1>Gestion des Articles</h1>
    <div>
        <a href="add_article.php" class="btn btn-addarticle">Ajouter un article</a>
        <a href="../auth/logout.php" class="btn btn-logout">Déconnexion</a>
    </div>
</div>

            <!-- Affichage des messages de succès -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Tableau des articles -->
<!-- Tableau des articles -->
<!-- Tableau des articles -->
<div class="articles-table">
    <h2>Liste des Articles</h2>
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
                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                    <td><?php echo htmlspecialchars($article['author']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></td>
                    <td>
                        <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-action btn-edit">
                            ✏️ Modifier
                        </a>
                        <a href="delete_article.php?id=<?php echo $article['id']; ?>" 
                           class="btn btn-action btn-delete" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                            🗑 Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>