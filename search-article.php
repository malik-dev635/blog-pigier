<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/loader.php';
session_start();

// Initialiser les variables de recherche
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'newest'; // Nouveau paramètre de tri

// Construire la requête SQL de base
$sql = "SELECT 
            a.*, 
            c.name AS category_name, 
            u.username AS author, 
            u.profile_picture AS author_profile,
            GROUP_CONCAT(DISTINCT cb.name) AS author_badges
        FROM 
            articles a
        LEFT JOIN 
            article_category ac ON a.id = ac.article_id
        LEFT JOIN 
            categories c ON ac.category_id = c.id
        LEFT JOIN 
            users u ON a.author_id = u.id
        LEFT JOIN
            user_badges ub ON u.id = ub.user_id
        LEFT JOIN
            contributor_badges cb ON ub.badge_id = cb.id";

// Ajouter les conditions de recherche
$where_conditions = [];
$params = [];

if (!empty($search_term)) {
    $where_conditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
    $params[] = "%{$search_term}%";
    $params[] = "%{$search_term}%";
}

if ($category_id > 0) {
    $where_conditions[] = "c.id = ?";
    $params[] = $category_id;
}

// Finaliser la requête
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " GROUP BY a.id";

// Ajouter l'ordre de tri en fonction du paramètre sort_by
switch ($sort_by) {
    case 'oldest':
        $sql .= " ORDER BY a.created_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY a.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY a.title DESC";
        break;
    case 'most_viewed':
        $sql .= " ORDER BY a.views DESC, a.created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY a.created_at DESC";
        break;
}

// Exécuter la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories pour le filtre
$categories_query = "SELECT * FROM categories ORDER BY name";
$stmt = $pdo->prepare($categories_query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Articles | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="shortcut icon" href="img/logo.png" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <?php require_once __DIR__ . '/includes/loader.php'; ?>

    <!-- Navbar -->
    <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
        <!-- Modal Bootstrap -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-success text-white">
                    <div class="modal-header">
                        <h5 class="modal-title">Connexion Réussie</h5>
                    </div>
                    <div class="modal-body">
                        ✅ Bienvenue <?= htmlspecialchars($_SESSION['username'] ?? '') ?> ! Vous êtes maintenant connecté.
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid px-4 px-lg-5">
            <!-- Logo -->
            <a class="navbar-brand fw-bold" href="index.php" style="color: #020268">
                <img src="img/logo.png" alt="Logo" style="height: 40px" />
                blog
            </a>

            <!-- Bouton Toggler pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Liens de navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark active" href="search-article.php">Articles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="about.php">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="contact.php">Contact</a>
                    </li>
                </ul>

                <!-- Barre de recherche -->
                <div class="d-flex align-items-center ms-3">
                    <!-- Icône de loupe -->
                    <button id="searchToggle" class="btn btn-link text-dark p-0">
                        <i class="fas fa-search"></i>
                    </button>
                    <!-- Barre de recherche -->
                    <div id="searchBar" class="search-bar">
                        <form action="search-article.php" method="GET" class="d-flex align-items-center">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher..." id="searchInput" />
                            <button id="closeSearch" class="btn btn-link text-dark p-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Utilisateur connecté : Affichage du profil -->
                    <div class="dropdown ms-3">
                        <button class="btn dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <img src="<?= htmlspecialchars($_SESSION['profile_picture'] ?? 'img-profile/default-avatar.png') ?>" 
                                alt="Profil" class="rounded-circle" style="width: 40px; height: 40px;">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-danger fw-bold" href="admin/dashboard.php">Tableau de bord</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="profile.php">Mon Profil</a></li>
                            <li><a class="dropdown-item" href="auth/logout.php">Se Déconnecter</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Utilisateur non connecté : Bouton de connexion -->
                    <a href="auth/login.php" class="btn btn-primary ms-3">Se Connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

   <!-- Section des articles -->
<section class="custom-blog-section py-5" id="articles">
    <div class="container">
        <h2 class="custom-section-title text-center mb-5">Tous les Articles</h2>

        <!-- Section de filtrage -->
        <div class="container mt-4">
            <div class="card border-0 mb-4">
                <div class="card-body p-4" style="background: linear-gradient(to right, #f8f9fa, #ffffff);">
                    <form action="" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label text-secondary fw-semibold">
                                <i class="fas fa-search me-2"></i>Rechercher
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg border-0" 
                                   id="search" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search_term); ?>" 
                                   placeholder="Rechercher un article...">
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label text-secondary fw-semibold">
                                <i class="fas fa-tag me-2"></i>Catégorie
                            </label>
                            <select class="form-select form-select-lg border-0" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label text-secondary fw-semibold">
                                <i class="fas fa-sort me-2"></i>Trier par
                            </label>
                            <select class="form-select form-select-lg border-0" id="sort_by" name="sort_by">
                                <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Plus récents</option>
                                <option value="oldest" <?php echo ($sort_by == 'oldest') ? 'selected' : ''; ?>>Plus anciens</option>
                                <option value="title_asc" <?php echo ($sort_by == 'title_asc') ? 'selected' : ''; ?>>Titre (A-Z)</option>
                                <option value="title_desc" <?php echo ($sort_by == 'title_desc') ? 'selected' : ''; ?>>Titre (Z-A)</option>
                                <option value="most_viewed" <?php echo ($sort_by == 'most_viewed') ? 'selected' : ''; ?>>Plus vus</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-lg w-100" style="transition: all 0.3s ease;">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
        .filter-section {
            background: linear-gradient(to right, #ffffff, #f8f9fa);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }

        .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .btn-primary {
            padding: 0.6rem 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }
        </style>

        <!-- Afficher le message de recherche si un terme de recherche est fourni -->
        <?php if (!empty($search_term)) : ?>
            <div class="row mb-4">
                <div class="col-12">
                    <p class="search-results-message">Résultats de la recherche pour : <strong>"<?= htmlspecialchars($search_term) ?>"</strong></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="row gx-4 gy-4">
            <?php if (empty($articles)) : ?>
                <!-- Aucun article trouvé -->
                <div class="col-12">
                    <p>Aucun article trouvé pour "<?= htmlspecialchars($search_term) ?>".</p>
                </div>
            <?php else : ?>
                <!-- Afficher tous les articles ou les articles filtrés -->
                <?php foreach ($articles as $article) : ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="article.php?id=<?= $article['id'] ?>" class="text-decoration-none">
                            <div class="custom-article-card">
                                <img src="<?= htmlspecialchars($article['image']) ?>" class="custom-article-img" alt="Article" />
                                <div class="custom-article-content">
                                    <span class="custom-badge"><?= htmlspecialchars($article['category_name']) ?></span>
                                    <h5 class="custom-article-title"><?= htmlspecialchars($article['title']) ?></h5>
                                    <div class="custom-article-meta">
                                        <img src="<?= htmlspecialchars($article['author_profile']) ?>" alt="Photo de profil de <?= htmlspecialchars($article['author']) ?>" class="custom-article-author-img" />
                                        <div>
                                        <p class="custom-article-date">
    <i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($article['created_at'])) ?>
</p>
<p class="custom-article-author">
    <i class="fas fa-user"></i> Par <strong><?= htmlspecialchars($article['author']) ?></strong>
</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

    <!-- Footer -->
    <?php include 'component/footer.php' ?>

    <!-- Scripts -->
    <script>
        // Script du loader
        window.addEventListener('load', () => {
            const loader = document.querySelector('.loader-wrapper');
            document.body.classList.add('loaded');
            
            setTimeout(() => {
                loader.classList.add('loader-hidden');
                loader.addEventListener('transitionend', () => {
                    document.body.removeChild(loader);
                });
            }, 500);
        });

        document.addEventListener("scroll", () => {
            const navbar = document.querySelector(".navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>