<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories avec le nombre correct d'articles
try {
    $sql = "SELECT c.*, 
            (SELECT COUNT(*) FROM article_category ac 
             INNER JOIN articles a ON ac.article_id = a.id 
             WHERE ac.category_id = c.id) as article_count 
            FROM categories c 
            ORDER BY c.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Catégories | Blog Pigier</title>
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
                <h1><i class="fas fa-tags me-2"></i>Gestion des Catégories</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus"></i>
                        Nouvelle Catégorie
                    </button>
                </div>
            </div>

            <!-- Categories Table Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Liste des Catégories</h2>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher une catégorie...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="categoriesTable">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Articles</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($category['name']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($category['description'] ?? 'Aucune description') ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $category['article_count'] ?> article(s)</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="#" class="btn btn-warning btn-sm edit-btn" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#editCategoryModal" 
                                               data-id="<?php echo $category['id']; ?>" 
                                               data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                               data-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <?php if ($category['article_count'] == 0): ?>
                                                <a href="delete_category.php?id=<?php echo $category['id']; ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
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

    <!-- Modal Ajout Catégorie -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_category.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Nom de la catégorie</label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
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

    <!-- Modal Modification Catégorie -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la Catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_category.php" method="POST">
                    <input type="hidden" id="editCategoryId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Nom de la catégorie</label>
                            <input type="text" class="form-control" id="editCategoryName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editCategoryDescription" name="description" rows="3"></textarea>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des boutons d'édition
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const description = this.getAttribute('data-description');
                    
                    document.getElementById('editCategoryId').value = id;
                    document.getElementById('editCategoryName').value = name;
                    document.getElementById('editCategoryDescription').value = description;
                });
            });

            // Fonction de recherche
            document.getElementById('searchInput').addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('#categoriesTable tbody tr');
                
                rows.forEach(row => {
                    const name = row.querySelector('td:first-child').textContent.toLowerCase();
                    const description = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    
                    if (name.includes(searchValue) || description.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
