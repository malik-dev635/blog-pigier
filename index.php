<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/loader.php';
session_start()
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blog Pigier</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/style.css" />
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
    <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
/>
    <link rel="shortcut icon" href="img/logo.png" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  </head>
  <body>
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
                    <a class="nav-link text-dark active" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="search-article.php">Articles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#events">Événements</a>
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
                        <img src="<?php echo isset($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'img-profile/default-avatar.png'; ?>" 
                            alt="Profil" class="rounded-circle" style="width: 40px; height: 40px;">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor')): ?>
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

    <!-- Section Hero -->
    <header class="hero-section">
      <div class="container-fluid hero-content">
        <div class="text-content">
          <h1 class="fw-bold">
            Bienvenue sur le Blog de pigier
            <span class="fancy-text">Yakro</span>
          </h1>
          <p class="lead">
            Découvrez les derniers articles et actualités de votre école
          </p>
          <a href="#articles" class="btn btn-outline-dark mt-3"
            >Voir les Articles</a
          >
          
        </div>
        <div class="card-stack">
          <div class="card-hero card-1"></div>
          <div class="card-hero card-2"></div>
          <div class="card-hero card-3"></div>
        </div>
      </div>
    </header>


    <?php

// Récupérer le dernier article avec auteur, catégorie et photo de profil
$stmt = $pdo->query("
    SELECT articles.*, users.username AS author, users.profile_picture AS author_profile, categories.name AS category_name 
    FROM articles 
    JOIN users ON articles.author_id = users.id 
    JOIN article_category ON articles.id = article_category.article_id 
    JOIN categories ON article_category.category_id = categories.id 
    ORDER BY articles.created_at DESC 
    LIMIT 1
");
$featured_article = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les deux articles suivants avec auteur, catégorie et photo de profil
$stmt = $pdo->query("
    SELECT articles.*, users.username AS author, users.profile_picture AS author_profile, categories.name AS category_name 
    FROM articles 
    JOIN users ON articles.author_id = users.id 
    JOIN article_category ON articles.id = article_category.article_id 
    JOIN categories ON article_category.category_id = categories.id 
    ORDER BY articles.created_at DESC 
    LIMIT 1, 2
");
$small_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT 
            a.*, 
            c.name AS category_name, 
            u.username AS author, 
            u.profile_picture AS author_profile
        FROM 
            categories c
        LEFT JOIN 
            article_category ac ON c.id = ac.category_id
        LEFT JOIN 
            articles a ON ac.article_id = a.id
        LEFT JOIN 
            users u ON a.author_id = u.id
        WHERE 
            a.id IS NOT NULL
        ORDER BY 
            c.name, a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grouper les articles par catégorie et limiter à 3 par catégorie
$articles_by_category = [];
foreach ($all_articles as $article) {
    $category_name = $article['category_name'];
    if (!isset($articles_by_category[$category_name])) {
        $articles_by_category[$category_name] = [];
    }
    
    // N'ajouter que si nous avons moins de 3 articles pour cette catégorie
    if (count($articles_by_category[$category_name]) < 3) {
        $articles_by_category[$category_name][] = $article;
    }
}
?>


<section class="custom-blog-section py-5" id="articles">
    <div class="container">
        <!-- 📰 Partie "À la Une" -->
        <h2 class="custom-section-title mb-4 mt-4">À la Une</h2>
        <div class="row gx-4 gy-4">
            <!-- Grand Article -->
            <div class="col-lg-8">
                <div class="custom-featured-article border-0">
                    <img src="<?= htmlspecialchars($featured_article['image']) ?>" class="custom-featured-img" alt="Dernier article" />
                    <div class="custom-featured-content">
                        <span class="custom-badge"><?= htmlspecialchars($featured_article['category_name']) ?></span>
                        <h3 class="custom-featured-title">
                            <?= $featured_article['title'] ?>
                        </h3>
                        <p class="custom-featured-text">
                        <?= html_entity_decode(substr($featured_article['content'], 0, 300)) . '...' ?>

                        </p>
                        <div class="custom-featured-meta">
                            <img src="<?= htmlspecialchars($featured_article['author_profile']) ?>" alt="Photo de profil de <?= htmlspecialchars($featured_article['author']) ?>" class="custom-featured-author-img" />
                            <div>
                            <p class="custom-article-date">
    <i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($article['created_at'])) ?>
</p>
<p class="custom-article-author">
    <i class="fas fa-user"></i> Par <strong><?= htmlspecialchars($article['author']) ?></strong>
</p>
                            </div>
                        </div>
                        <a href="article.php?id=<?= $featured_article['id'] ?>" class="custom-featured-button">Lire l'article</a>
                    </div>
                </div>
            </div>

            <!-- Deux petits articles à droite -->
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4 h-100">
                    <?php foreach ($small_articles as $article) : ?>
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
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

       <!-- Section des articles par catégorie -->
        <?php foreach ($articles_by_category as $category_name => $articles) : ?>
            <h2 class="custom-section-title mb-4 mt-6"><?= htmlspecialchars($category_name) ?></h2>
            <div class="row gx-4 gy-4">
                <?php foreach ($articles as $article) : ?>
                    <div class="col-lg-4 col-md-6 article-item">
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
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Section Citation de la Semaine -->
<?php
// Définir des valeurs par défaut pour la citation
$quote_text = "Le succès, c'est tomber sept fois et se relever huit fois.";
$quote_author = "Proverbe japonais";

// Essayer de récupérer la citation de la semaine depuis la base de données
try {
    $quote_query = "SELECT * FROM site_content WHERE content_key = 'weekly_quote' LIMIT 1";
    $stmt = $pdo->prepare($quote_query);
    $stmt->execute();
    $quote_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si une citation existe dans la base de données, l'utiliser
    if ($quote_data) {
        $quote_content = json_decode($quote_data['content_value'], true);
        $quote_text = $quote_content['text'] ?? $quote_text;
        $quote_author = $quote_content['author'] ?? $quote_author;
    }
} catch (PDOException $e) {
    // En cas d'erreur (table inexistante, etc.), utiliser les valeurs par défaut
    // Pas besoin de faire quoi que ce soit car les valeurs par défaut sont déjà définies
}
?>
<section class="quote-section py-5" style="background: linear-gradient(135deg, #020268 0%, #1e1ef0 100%);" id="quote">
    <div class="container">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        <?php else: ?>
            <?php 
                // Supprimer les messages de la session pour les non-administrateurs
                if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
                if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
            ?>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="quote-wrapper p-5 position-relative">
                    <div class="quote-decoration-left">
                        <i class="fas fa-quote-left fa-4x" style="color: rgba(255, 255, 255, 0.1);"></i>
                    </div>
                    <div class="quote-decoration-right">
                        <i class="fas fa-quote-right fa-4x" style="color: rgba(255, 255, 255, 0.1);"></i>
                    </div>
                    <div class="text-center">
                        <h2 class="text-white mb-4 fw-bold">Citation de la Semaine</h2>
                        <p class="quote-text display-5 text-white mb-4 fw-light" id="quote-text"><?= htmlspecialchars($quote_text) ?></p>
                        <div class="quote-author-container">
                            <p class="quote-author text-white-50 fs-4 fst-italic" id="quote-author">— <?= htmlspecialchars($quote_author) ?></p>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <div class="edit-quote-btn">
                            <button type="button" class="btn btn-sm btn-link text-white-50" data-bs-toggle="modal" data-bs-target="#editQuoteModal">
                                <i class="fas fa-pen"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.quote-section {
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.quote-wrapper {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
}

.quote-decoration-left {
    position: absolute;
    top: 20px;
    left: 20px;
    opacity: 0.7;
}

.quote-decoration-right {
    position: absolute;
    bottom: 20px;
    right: 20px;
    opacity: 0.7;
}

.quote-text {
    font-style: italic;
    line-height: 1.6;
    position: relative;
    z-index: 2;
}

.quote-author-container {
    display: inline-block;
    position: relative;
    margin-top: 20px;
}

.quote-author {
    position: relative;
    z-index: 2;
}

.quote-section::before {
    content: '';
    position: absolute;
    top: -50px;
    left: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.quote-section::after {
    content: '';
    position: absolute;
    bottom: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

@media (max-width: 768px) {
    .quote-text {
        font-size: 1.5rem !important;
    }
    
    .quote-author {
        font-size: 1.1rem !important;
    }
    
    .quote-decoration-left i,
    .quote-decoration-right i {
        font-size: 2rem;
    }
}

.edit-quote-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.edit-quote-btn:hover {
    opacity: 1;
}

.edit-quote-btn .btn-link {
    text-decoration: none;
}

/* Style pour le bouton d'édition des événements */
.edit-events-btn {
    position: absolute;
    top: 0;
    right: 0;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.edit-events-btn:hover {
    opacity: 1;
}

.edit-events-btn .btn-link {
    text-decoration: none;
    font-size: 1.1rem;
}
</style>

<!-- Section Événements à Venir -->
<?php
// Récupérer les événements à venir
$events = [];
try {
    $events_query = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3";
    $stmt = $pdo->prepare($events_query);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la table n'existe pas, on continue avec un tableau vide
    // Pas besoin de faire quoi que ce soit car $events est déjà initialisé comme un tableau vide
}

// Afficher la section uniquement s'il y a des événements ou si l'utilisateur est admin
if (!empty($events) || (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin')):
?>
<section class="events-section py-5" id="events">
    <div class="container">
        <div class="text-center mb-5 position-relative">
            <h2 class="custom-section-title">Événements à Venir</h2>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                <div class="edit-events-btn">
                    <a href="admin/manage_events.php" class="btn-link text-secondary">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
                
                <?php if (isset($_SESSION['event_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                            echo $_SESSION['event_success']; 
                            unset($_SESSION['event_success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['event_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php 
                            echo $_SESSION['event_error']; 
                            unset($_SESSION['event_error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php
                    // Pour les non-admins, on s'assure que les messages ne sont pas conservés
                    if (isset($_SESSION['event_success'])) unset($_SESSION['event_success']);
                    if (isset($_SESSION['event_error'])) unset($_SESSION['event_error']);
                ?>
            <?php endif; ?>
        </div>
        
        <div class="row g-4">
            <?php if (empty($events) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                <!-- Message pour les administrateurs quand il n'y a pas d'événements -->
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucun événement à venir n'est programmé. <a href="admin/manage_events.php" class="alert-link">Cliquez ici</a> pour en ajouter.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="event-card h-100">
                            <div class="event-date">
                                <span class="day"><?= date('d', strtotime($event['event_date'])) ?></span>
                                <span class="month"><?= date('M', strtotime($event['event_date'])) ?></span>
                            </div>
                            <div class="event-content">
                                <h3><?= htmlspecialchars($event['title']) ?></h3>
                                <p class="event-time"><i class="far fa-clock me-2"></i><?= htmlspecialchars($event['time']) ?></p>
                                <p class="event-location"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></p>
                                <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                                <a href="#" class="btn btn-primary">En savoir plus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section Top Contributeurs -->
<?php
// Récupérer les meilleurs contributeurs (basé sur le nombre d'articles)
$top_contributors_query = "
    SELECT 
        u.id,
        u.username,
        u.profile_picture,
        (SELECT COUNT(*) FROM articles WHERE author_id = u.id) as article_count,
        GROUP_CONCAT(DISTINCT CONCAT(cb.name, ':', cb.color) SEPARATOR '|') as badge_info
    FROM users u
    LEFT JOIN user_badges ub ON u.id = ub.user_id
    LEFT JOIN contributor_badges cb ON ub.badge_id = cb.id
    GROUP BY u.id
    HAVING article_count > 0
    ORDER BY article_count DESC
    LIMIT 3
";
$stmt = $pdo->query($top_contributors_query);
$top_contributors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($top_contributors)): ?>
<section class="contributors-section py-5" style="background-color: #f8f9fa;" id="contributors">
    <div class="container">
        <h2 class="custom-section-title text-center mb-5">Top Contributeurs</h2>
        <div class="row g-4 justify-content-center">
            <?php foreach ($top_contributors as $index => $contributor): 
                $badge_info = [];
                if (!empty($contributor['badge_info'])) {
                    $badges_array = explode('|', $contributor['badge_info']);
                    foreach ($badges_array as $badge_data) {
                        if (!empty($badge_data)) {
                            list($name, $color) = explode(':', $badge_data);
                            $badge_info[] = ['name' => $name, 'color' => $color];
                        }
                    }
                }
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="contributor-card text-center">
                        <?php if ($index === 0): ?>
                            <div class="contributor-badge">
                                <i class="fas fa-crown"></i>
                            </div>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($contributor['profile_picture'] ?: 'img-profile/default-avatar.png') ?>" 
                             alt="<?= htmlspecialchars($contributor['username']) ?>" 
                             class="contributor-image">
                        <h4><?= htmlspecialchars($contributor['username']) ?></h4>
                        <p class="text-muted"><?= $contributor['article_count'] ?> article<?= $contributor['article_count'] > 1 ? 's' : '' ?> publié<?= $contributor['article_count'] > 1 ? 's' : '' ?></p>
                        <div class="contributor-badges">
                            <?php foreach ($badge_info as $badge): ?>
                                <a href="search-article.php?badge=<?= urlencode(htmlspecialchars($badge['name'])) ?>" class="text-decoration-none">
                                    <span class="badge user-badge" style="background-color: <?= htmlspecialchars($badge['color']) ?>; cursor: pointer;">
                                        <?= htmlspecialchars($badge['name']) ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- Section Campus Life -->
<section class="campus-life-section py-5" id="campus-life">
    <div class="container">
        <h2 class="custom-section-title text-center mb-5">Campus Life</h2>
        <div class="campus-gallery">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="campus-card">
                        <img src="img/img-batiment.jpg" alt="Campus Life" class="img-fluid">
                        <div class="campus-overlay">
                            <div class="campus-content">
                                <h4>Vie Étudiante</h4>
                                <p>Moments de partage et d'apprentissage</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="campus-card">
                        <img src="img/img-batiment.jpg" alt="Campus Life" class="img-fluid">
                        <div class="campus-overlay">
                            <div class="campus-content">
                                <h4>Activités</h4>
                                <p>Développement personnel et professionnel</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="campus-card">
                        <img src="img/img-batiment.jpg" alt="Campus Life" class="img-fluid">
                        <div class="campus-overlay">
                            <div class="campus-content">
                                <h4>Innovation</h4>
                                <p>Laboratoires et espaces de création</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'component/footer.php' ?>
<style>
/* Styles pour les événements */
.event-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
    position: relative;
    padding: 20px;
}

.event-card:hover {
    transform: translateY(-5px);
}

.event-date {
    background: #020268;
    color: white;
    text-align: center;
    padding: 10px;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 15px;
}

.event-date .day {
    font-size: 24px;
    font-weight: bold;
    display: block;
}

.event-date .month {
    font-size: 16px;
    text-transform: uppercase;
}

.event-content h3 {
    color: #333;
    margin-bottom: 15px;
}

/* Styles pour les contributeurs */
.contributor-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    position: relative;
}

.contributor-card:hover {
    transform: translateY(-5px);
}

.contributor-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 3px solid #020268;
}

.contributor-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: gold;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.contributor-badges {
    margin-top: 10px;
}

.contributor-badges .badge {
    margin: 0 3px;
}

/* Styles pour Campus Life */
.campus-card {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 20px;
}

.campus-card img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 15px;
    transition: transform 0.3s ease;
}

.campus-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 20px;
    color: white;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.campus-card:hover .campus-overlay {
    opacity: 1;
}

.campus-card:hover img {
    transform: scale(1.1);
}

/* Style pour la section Citation */
.quote-wrapper {
    position: relative;
    border-radius: 15px;
}

.quote-text {
    font-style: italic;
    line-height: 1.6;
}

.quote-author {
    font-size: 1.1rem;
    margin-top: 20px;
}

/* Style amélioré pour la barre latérale des réseaux sociaux */
.social-sidebar {
    position: fixed;
    right: -60px; /* Commencer hors de l'écran */
    top: 50%;
    transform: translateY(-50%);
    display: flex; /* Toujours en flex pour que la transition fonctionne */
    flex-direction: column;
    gap: 15px;
    z-index: 1000;
    padding: 15px;
    opacity: 0; /* Commencer avec une opacité de 0 */
    transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); /* Transition avec effet rebond */
    pointer-events: none; /* Désactiver les interactions quand invisible */
}

.social-sidebar.visible {
    opacity: 1;
    right: 0; /* Position finale */
    pointer-events: auto; /* Réactiver les interactions */
}

.social-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transform: scale(0.8); /* Commencer légèrement plus petit */
    opacity: 0; /* Commencer invisible */
    transition: all 0.3s ease;
}

.social-sidebar.visible .social-icon {
    transform: scale(1); /* Taille normale quand visible */
    opacity: 1; /* Pleinement visible */
}

/* Délai d'apparition pour chaque icône */
.social-sidebar.visible .social-icon:nth-child(1) {
    transition-delay: 0.1s;
}
.social-sidebar.visible .social-icon:nth-child(2) {
    transition-delay: 0.2s;
}
.social-sidebar.visible .social-icon:nth-child(3) {
    transition-delay: 0.3s;
}
.social-sidebar.visible .social-icon:nth-child(4) {
    transition-delay: 0.4s;
}

.social-icon:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: inherit;
    filter: brightness(90%);
    transition: all 0.3s ease;
    opacity: 0;
}

.social-icon i {
    color: white;
    font-size: 20px;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.social-icon:hover {
    transform: translateX(-10px) scale(1.1);
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);
}

.social-icon:hover:before {
    opacity: 1;
}

.social-icon.linkedin {
    background: linear-gradient(135deg, #0077B5, #00669c);
}

.social-icon.whatsapp {
    background: linear-gradient(135deg, #25D366, #20b356);
}

.social-icon.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-icon.facebook {
    background: linear-gradient(135deg, #1877F2, #0d5cc7);
}

.social-icon a {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

@media (max-width: 768px) {
    .social-sidebar {
        padding: 10px;
    }
    
    .social-icon {
        width: 40px;
        height: 40px;
    }
    
    .social-icon i {
        font-size: 18px;
    }
}
</style>

<div class="social-sidebar">
    <div class="social-icon linkedin">
        <a href="https://www.linkedin.com/school/pigiercotedivoire/" target="_blank">
            <i class="fab fa-linkedin"></i>
        </a>
    </div>
    <div class="social-icon whatsapp">
        <a href="https://www.whatsapp.com" target="_blank">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    <div class="social-icon instagram">
        <a href="https://www.instagram.com" target="_blank">
            <i class="fab fa-instagram"></i>
        </a>
    </div>
    <div class="social-icon facebook">
        <a href="https://www.facebook.com/profile.php?id=100090831890714" target="_blank">
            <i class="fab fa-facebook"></i>
        </a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Gestion de l'apparition de la barre latérale des réseaux sociaux
        const socialSidebar = document.querySelector('.social-sidebar');
        const articlesSection = document.querySelector('#articles');
        
        if (socialSidebar && articlesSection) {
            // Fonction pour vérifier si l'utilisateur a défilé jusqu'à la section des articles
            function checkScroll() {
                const articlesSectionTop = articlesSection.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                // Si la section des articles est visible (ou a été dépassée)
                if (articlesSectionTop < windowHeight * 0.75) {
                    if (!socialSidebar.classList.contains('visible')) {
                        // Ajouter la classe avec un petit délai pour créer un effet plus naturel
                        setTimeout(() => {
                            socialSidebar.classList.add('visible');
                        }, 300);
                    }
                } else {
                    socialSidebar.classList.remove('visible');
                }
            }
            
            // Vérifier au chargement initial
            setTimeout(checkScroll, 500); // Petit délai au chargement initial
            
            // Vérifier lors du défilement
            window.addEventListener('scroll', checkScroll);
        }
        
        // Gestion du défilement fluide pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === "#") return; // Ignorer les liens vides
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80, // Offset pour la navbar fixe
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Code existant pour la navbar
        document.addEventListener("scroll", () => {
            const navbar = document.querySelector(".navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    });
</script>





    <script>
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
    
    <!-- Modal d'édition de la citation -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
    <div class="modal fade" id="editQuoteModal" tabindex="-1" aria-labelledby="editQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editQuoteModalLabel">Modifier la citation de la semaine</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin/update_quote.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="quote_text" class="form-label">Citation</label>
                            <textarea class="form-control" id="quote_text" name="quote_text" rows="4" required><?= htmlspecialchars($quote_text) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="quote_author" class="form-label">Auteur</label>
                            <input type="text" class="form-control" id="quote_author" name="quote_author" value="<?= htmlspecialchars($quote_author) ?>" required>
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
    <?php endif; ?>
  </body>
</html>

