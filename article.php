<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/loader.php';
session_start();

// Vérifier si l'ID de l'article est passé dans l'URL
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$article_id = intval($_GET['id']); // Récupérer et valider l'ID de l'article


// Récupérer l'article avec l'auteur et la catégorie
$query = "
    SELECT articles.*, users.username AS author, users.profile_picture AS author_profile, categories.name AS category_name 
    FROM articles 
    JOIN users ON articles.author_id = users.id 
    JOIN article_category ON articles.id = article_category.article_id 
    JOIN categories ON article_category.category_id = categories.id 
    WHERE articles.id = :article_id
";
$stmt = $pdo->prepare($query);
$stmt->execute([':article_id' => $article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// Après la récupération de l'article principal, ajoutez ce code pour récupérer les articles similaires
$query_similar = "
    SELECT DISTINCT a.*, u.username AS author 
    FROM articles a
    JOIN users u ON a.author_id = u.id
    JOIN article_category ac1 ON a.id = ac1.article_id
    JOIN article_category ac2 ON ac1.category_id = ac2.category_id
    WHERE ac2.article_id = :current_article_id
    AND a.id != :current_article_id
    ORDER BY a.created_at DESC
    LIMIT 2";

$stmt = $pdo->prepare($query_similar);
$stmt->execute([
    ':current_article_id' => $article_id
]);
$similar_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les commentaires de l'article
// Récupérer les commentaires de l'article avec les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT c.*, u.username, u.profile_picture 
                           FROM comments c 
                           JOIN users u ON c.email = u.email 
                           WHERE c.article_id = :article_id 
                           ORDER BY c.created_at DESC");
    $stmt->execute([':article_id' => $article['id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $comment_count = isset($comments) ? count($comments) : 0;
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}


// Vérifier si l'article existe
if (!$article) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($article['title']) ?> | Blog Pigier</title>
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
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8f9fa;
        }

        .article-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
            padding: 0 1rem;
        }

        .article-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .article-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .article-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .article-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }

        .sidebar {
            background-color: #fff;
            position: sticky;
            top: 20px;
            height: fit-content;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .sidebar-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .sidebar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .sidebar-card h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #004494;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .sidebar-card p {
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .sidebar-card .btn {
            width: 100%;
            padding: 8px;
            background-color: #004494;
            border: none;
            transition: background-color 0.3s ease;
        }

        .sidebar-card .btn:hover {
            background-color: #003377;
        }

        .text-muted i {
            width: 16px;
            text-align: center;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #004494;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .social-links a:hover {
            background-color: #003366;
        }

        @media (max-width: 768px) {
            .article-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                top: 0;
                order: 1;
                margin-top: 20px;
            }

            .article-content {
                order: 0;
            }
        }

        .article-strip {
            display: flex;
            background: white;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100px;
        }

        .article-strip:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .article-strip-image {
            width: 100px;
            min-width: 100px;
            height: 100px;
        }

        .article-strip-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .article-strip-content {
            padding: 10px;
            flex-grow: 1;
            overflow: hidden;
        }

        .article-strip h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-strip-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .article-strip-meta span {
            display: block;
            margin-bottom: 2px;
        }

        .article-strip-meta i {
            width: 16px;
            color: #004494;
        }

        .stretched-link::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1;
            content: "";
        }

        @media (max-width: 768px) {
            
            .article-strip {
                height: 80px;
            }

            .article-strip-image {
                width: 80px;
                min-width: 80px;
                height: 80px;
            }

            .article-strip h4 {
                font-size: 0.9rem;
                -webkit-line-clamp: 1;
            }
        }

        .blue-card {
            position: relative;
            cursor: pointer;
        }

        .blue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 68, 148, 0.3);
        }

        .blue-card a {
            color: inherit;
        }

        .blue-card:hover::after {
            content: "Visiter le site →";
            position: absolute;
            bottom: 15px;
            right: 15px;
            color: white;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .events-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
        }

        .events-card:hover {
            transform: none;
            box-shadow: none;
        }

        .event-item {
            display: flex;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .event-date {
            background: #004494;
            color: white;
            padding: 8px;
            border-radius: 8px;
            text-align: center;
            min-width: 60px;
            margin-right: 15px;
        }

        .event-day {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            line-height: 1;
        }

        .event-month {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .event-info {
            flex: 1;
        }

        .event-info h5 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: #333;
        }

        .event-description {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 8px;
        }

        .event-location {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 3px;
        }

        .event-location i {
            color: #004494;
            width: 16px;
        }

        /* Cartes d'articles similaires avec des couleurs variées */
        .article-strip {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-left: 4px solid #004494;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Style pour les événements */
        .events-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-radius: 15px;
        }

        .event-item {
            background: white;
            margin-bottom: 10px;
            border-radius: 10px;
            padding: 15px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .event-date {
            background: linear-gradient(135deg, #FF6B6B, #FF8E8E);
            border-radius: 10px;
            padding: 10px;
            color: white;
            box-shadow: 0 3px 6px rgba(255,107,107,0.2);
        }

        /* Style pour la carte Pigier */
        .blue-card {
            background: #2e475d;
            border-radius: 15px;
            overflow: hidden;
        }

        .blue-card img {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }

        .blue-card:hover img {
            transform: scale(1.05);
        }

        /* Style pour les liens sociaux */
        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .social-links .facebook {
            background: #1877F2;
        }

        .social-links .instagram {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        }

        .social-links .linkedin {
            background: #0077B5;
        }

        .social-links a:hover {
            transform: translateY(-3px);
        }

        /* Style pour les titres de section */
        .sidebar h3 {
            color: #004494;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .sidebar h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, #004494, #006494);
            border-radius: 3px;
        }

        /* Style pour les informations des événements */
        .event-info h5 {
            color: #004494;
            font-weight: 600;
        }

        .event-location {
            color: #666;
        }

        .event-location i {
            color: #FF6B6B;
        }
    </style>
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
                    <a class="nav-link text-dark " href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark active" href="search-article.php">Articles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#events">Événements</a>
                </li>
               <!-- <li class="nav-item">
                    <a class="nav-link text-dark" href="about.php">À propos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="contact.php">Contact</a>
                </li>-->
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
    <div class="article-grid">
        <!-- Contenu de l'article -->
        <div class="article-content">
            <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
            <div class="article-meta">
    Publié le <?= date('d M Y', strtotime($article['created_at'])) ?> par 
                <strong><?= htmlspecialchars($article['author']) ?></strong>
</div>
            <img src="<?= htmlspecialchars($article['image']) ?>" alt="Article Image" class="article-image" />
            <div class="article-text">
                <?=$article['content'] ?>
            </div>
            <div class="comments-section mt-5">

            <h3 class="mb-4">Commentaires (<?= $comment_count ?>)</h3>

    <!-- Affichage des commentaires existants -->
    <div class="comments-list mb-4">
        <?php if (isset($comments) && count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment mb-4 p-3 bg-white">
                    <div class="d-flex align-items-start">
                        <!-- Photo de profil -->
                        <img src="<?= htmlspecialchars($comment['profile_picture']) ?>" 
                             alt="Photo de profil" 
                             class="rounded-circle me-3" 
                             style="width: 50px; height: 50px; object-fit: cover;">
                        <!-- Contenu du commentaire -->
                        <div class="flex-grow-1">
                            <div class="comment-author mb-2">
                                <strong><?= htmlspecialchars($comment['name']) ?></strong>
                                <small class="text-muted ms-2"> - <?= date('d M Y H:i', strtotime($comment['created_at'])) ?></small>
                            </div>
                            <div class="comment-text">
                                <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
        <?php endif; ?>
    </div>

    <!-- Formulaire de commentaire (visible uniquement si l'utilisateur est connecté) -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="comment-form">
            <h4 class="mb-3">Laisser un commentaire</h4>
            <div class="alert mt-4">
    <i class="fas fa-shield-alt me-2 text-primary"></i>
    <small>On est cool ici ! Pas de messages offensants, sinon on modère. 😎</small>
</div>
            <form action="post_comment.php" method="POST">
                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                <div class="mb-3">
                    <textarea name="comment" class="form-control" rows="4" placeholder="Votre commentaire..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Poster le commentaire</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p>Vous devez être connecté pour laisser un commentaire.</p>
            <a href="auth/login.php" class="btn btn-primary">Se connecter</a>
        </div>
    <?php endif; ?>
</div>
        </div>
<style>

</style>
        <!-- Sidebar avec cartes et liens -->
        <div class="sidebar" style="position: sticky;">
             <!-- Liens vers les réseaux sociaux -->
             <h3>Suivez-nous</h3>
             <div class="social-links mb-5">
        <a href="https://www.facebook.com/profile.php?id=100090831890714" target="_blank" class="facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" target="_blank" class="instagram">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://www.linkedin.com/school/pigiercotedivoire/" target="_blank" class="linkedin">
            <i class="fab fa-linkedin-in"></i>
        </a>
    </div>

    <!-- Articles similaires -->
    <h3 class="mb-4">Articles similaires</h3>
    <?php if (!empty($similar_articles)): ?>
        <?php foreach ($similar_articles as $similar): ?>
            <div class="article-strip">
                <div class="article-strip-image">
                    <img src="<?= htmlspecialchars($similar['image']) ?>" alt="<?= htmlspecialchars($similar['title']) ?>" />
                </div>
                <div class="article-strip-content">
                    <h4><?= htmlspecialchars($similar['title']) ?></h4>
                    <div class="article-strip-meta">
                        <span><i class="far fa-user me-1"></i><?= htmlspecialchars($similar['author']) ?></span>
                        <span><i class="far fa-calendar me-1"></i><?= date('d M Y', strtotime($similar['created_at'])) ?></span>
                    </div>
                    <a href="article.php?id=<?= $similar['id'] ?>" class="stretched-link"></a>
            </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="article-strip">
            <p class="text-muted mb-0">Aucun article similaire trouvé.</p>
        </div>
    <?php endif; ?>

         

            <!-- EVENEMENT -->
            <div class="sidebar-card events-card">
                <h4><i class="far fa-calendar-alt me-2"></i>Événements à venir</h4>
                <?php
                // Récupérer les 3 prochains événements
                $events_query = "SELECT * FROM events 
                                WHERE event_date >= CURDATE() 
                                ORDER BY event_date ASC 
                                LIMIT 3";
                $events_stmt = $pdo->query($events_query);
                $upcoming_events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($upcoming_events)): ?>
                    <div class="events-list">
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="event-item">
                                <div class="event-date">
                                    <span class="event-day"><?= date('d', strtotime($event['event_date'])) ?></span>
                                    <span class="event-month"><?= date('M', strtotime($event['event_date'])) ?></span>
                                </div>
                                <div class="event-info">
                                    <h5><?= htmlspecialchars($event['title']) ?></h5>
                                    <?php if (isset($event['description'])): ?>
                                        <p class="event-description">
                                            <?= htmlspecialchars(substr($event['description'], 0, 50)) ?>...
                                        </p>
                                    <?php endif; ?>
                                    <?php if (isset($event['location'])): ?>
                                        <p class="event-location">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($event['location']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
            </div>
                        <?php endforeach; ?>
</div>
                <?php else: ?>
                    <p class="text-muted text-center my-3">Aucun événement à venir</p>
                <?php endif; ?>
            </div>

              <!-- Publicité cliquable -->
            <div class="sidebar-card blue-card" style="background: linear-gradient(135deg, #2e475d, #2e475d);">
                <a href="https://pigierci.com" target="_blank" class="text-decoration-none stretched-link">
                    <img src="img/pigiercotedivoire_cover.jpeg" alt="Publicité" />
                    <h4 class="text-white">REJOIGNEZ PIGIER CÔTE D'IVOIRE</h4>
                    <p class="text-white-50">Formation d'excellence • Diplômes reconnus • Réseau international • Opportunités professionnelles</p>
                </a>
            </div>
        </div>
    </div>


    <?php include 'component/footer.php' ?>

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
    <script src="assets/js/script.js"></script>
</body>
</html>