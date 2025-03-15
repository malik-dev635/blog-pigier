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

// Récupérer tous les commentaires avec les informations des articles
try {
    $sql = "SELECT c.*, a.title as article_title, u.profile_picture,
            (SELECT COUNT(*) FROM comments WHERE parent_id = c.id) as replies_count
            FROM comments c 
            LEFT JOIN articles a ON c.article_id = a.id 
            LEFT JOIN users u ON c.email = u.email 
            WHERE c.parent_id IS NULL
            ORDER BY c.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Commentaires | Blog Pigier</title>
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
                <h1><i class="fas fa-comments me-2"></i>Gestion des Commentaires</h1>
            </div>

            <!-- Comments Table Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Liste des Commentaires</h2>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un commentaire...">
                        </div>
                        <select class="form-select" style="width: auto;" id="filterComments">
                            <option value="all">Tous les commentaires</option>
                            <option value="recent">Dernières 24h</option>
                            <option value="with_replies">Avec réponses</option>
                            <option value="no_replies">Sans réponses</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="commentsTable">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Article</th>
                                <th>Commentaire</th>
                                <th>Réponses</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="../<?= htmlspecialchars($comment['profile_picture'] ?? 'img/default-avatar.png') ?>" 
                                                 alt="Avatar" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                            <div>
                                                <span class="fw-bold"><?= htmlspecialchars($comment['name']) ?></span><br>
                                                <small class="text-muted"><?= htmlspecialchars($comment['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <a href="../article.php?id=<?= $comment['article_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($comment['article_title']) ?>
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <?= htmlspecialchars(substr($comment['comment'], 0, 100)) . (strlen($comment['comment']) > 100 ? '...' : '') ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if ($comment['replies_count'] > 0): ?>
                                            <span class="badge bg-info"><?= $comment['replies_count'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-action btn-edit" 
                                                    onclick="viewComment('<?= htmlspecialchars($comment['comment']) ?>', '<?= htmlspecialchars($comment['name']) ?>', '<?= htmlspecialchars($comment['article_title']) ?>')"
                                                    title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="delete_comment.php?id=<?= $comment['id'] ?>" 
                                               class="btn btn-action btn-delete" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ? Cela supprimera également toutes les réponses associées.');"
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

    <!-- Modal Voir Commentaire -->
    <div class="modal fade" id="viewCommentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails du Commentaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Auteur:</strong>
                        <p id="commentAuthor" class="mb-2"></p>
                        <strong>Article:</strong>
                        <p id="commentArticle" class="mb-2"></p>
                        <strong>Commentaire:</strong>
                        <p id="commentContent" class="mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fonction de recherche améliorée
        document.getElementById('searchInput').addEventListener('input', filterComments);
        document.getElementById('filterComments').addEventListener('change', filterComments);

        function filterComments() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const filterValue = document.getElementById('filterComments').value;
            const rows = document.querySelectorAll('#commentsTable tbody tr');
            
            rows.forEach(row => {
                const username = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const article = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const comment = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const replies = row.querySelector('td:nth-child(4)').textContent.trim();
                const date = new Date(row.querySelector('td:nth-child(5)').textContent);
                const now = new Date();
                const hoursDiff = (now - date) / (1000 * 60 * 60);

                let show = true;

                // Filtre de recherche
                if (!username.includes(searchValue) && 
                    !article.includes(searchValue) && 
                    !comment.includes(searchValue)) {
                    show = false;
                }

                // Filtre par catégorie
                if (show) {
                    switch(filterValue) {
                        case 'recent':
                            if (hoursDiff > 24) show = false;
                            break;
                        case 'with_replies':
                            if (replies === '-') show = false;
                            break;
                        case 'no_replies':
                            if (replies !== '-') show = false;
                            break;
                    }
                }

                row.style.display = show ? '' : 'none';
            });
        }

        // Fonction pour voir le contenu complet d'un commentaire
        function viewComment(content, author, article) {
            document.getElementById('commentAuthor').textContent = author;
            document.getElementById('commentArticle').textContent = article;
            document.getElementById('commentContent').textContent = content;
            new bootstrap.Modal(document.getElementById('viewCommentModal')).show();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 