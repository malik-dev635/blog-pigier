<?php
require_once __DIR__ . '/config/config.php';
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
            articles.*, 
            categories.name AS category_name, 
            users.username AS author, 
            users.profile_picture AS author_profile
        FROM 
            articles
        LEFT JOIN 
            article_category ON articles.id = article_category.article_id
        LEFT JOIN 
            categories ON article_category.category_id = categories.id
        LEFT JOIN 
            users ON articles.author_id = users.id
        GROUP BY 
            categories.id, articles.id
        ORDER BY 
            categories.name, articles.created_at DESC
        LIMIT 3"; // Limite de 3 articles par catégorie

$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grouper les articles par catégorie
$articles_by_category = [];
foreach ($all_articles as $article) {
    $category_name = $article['category_name'];
    if (!isset($articles_by_category[$category_name])) {
        $articles_by_category[$category_name] = [];
    }
    $articles_by_category[$category_name][] = $article;
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
<style>
    /* Style de la barre latérale des réseaux sociaux */
.social-sidebar {
    position: fixed;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 1000;
}


.social-icon {
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease, background-color 0.3s ease;
    cursor: pointer;
}


.social-icon i {
    color: white;
    font-size: 24px;
}

.social-icon.linkedin {
    background-color: #0A66C2; 
}

.social-icon.whatsapp {
    background-color: #25D366; 
}

.social-icon.instagram {
    background-color: #E4405F; 
}

.social-icon.facebook {
    background-color: #1877F2; 
}

/* Effet au survol */
.social-icon:hover {
    transform: translateX(-10px); 
}

.social-icon.linkedin:hover {
    background-color: #004182; 
}

.social-icon.whatsapp:hover {
    background-color: #128C7E; 
}

.social-icon.instagram:hover {
    background-color: #C13584; 
}

.social-icon.facebook:hover {
    background-color: #1156A3; 
}
.social-sidebar {
    position: fixed;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    display: none; 
    flex-direction: column;
    gap: 10px;
    z-index: 1000;
}
</style>
<script>
    document.addEventListener("scroll", function () {
    const socialSidebar = document.querySelector(".social-sidebar");
    const secondSection = document.querySelector("section:nth-of-type(1)"); // Sélectionne la deuxième section

    if (secondSection && window.scrollY > secondSection.offsetTop) {
        socialSidebar.style.display = "flex"; // Affiche la barre latérale
    } else {
        socialSidebar.style.display = "none"; // Cache la barre latérale
    }
});
</script>
<footer class="blog-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Section 1 : Branding -->
            <div class="footer-brand">
                <a class="navbar-brand fw-bold" href="index.php" style="color: #fff; font-size: 4rem; display: flex; align-items: center; margin-bottom: 15px;">
                    <img src="img/logo.png" alt="Logo" style="height: 70px; margin-right: 12px;" />
                    Blog
                </a>
                <p class="footer-description">
                    Explorez l'innovation et l'éducation à travers nos articles et ressources inspirants.
                </p>
            </div>

           <!-- Section 2 : Liens rapides -->
<div class="footer-links">
    <h4>Liens rapides</h4>
    <ul>
        <li><a href="#"> Accueil<i class="fa-sharp fa-solid fa-arrow-up-right-from-square"></i></a></li>
        <li><a href="#"> Articles<i class="fa-sharp fa-solid fa-arrow-up-right-from-square"></i></a></li>
        <li><a href="#"> À propos<i class="fa-sharp fa-solid fa-arrow-up-right-from-square"></i></a></li>
        <li><a href="https://www.pigierci.com/"> Site Officiel<i class="fa-sharp fa-solid fa-arrow-up-right-from-square"></i></a></li>
        <li><a href="#"> Contact<i class="fa-sharp fa-solid fa-arrow-up-right-from-square"></i></a></li>
    </ul>
</div>


            <!-- Section 3 : Newsletter -->
            <div class="footer-newsletter">
                <h4>Abonnez-vous</h4>
                <p>Recevez les derniers articles directement dans votre boîte mail.</p>
                <form>
                    <input type="email" placeholder="Votre email..." required>
                    <button type="submit">S'abonner</button>
                </form>
            </div>

            <!-- Section 4 : Réseaux sociaux -->
            <div class="footer-social">
                <h4>Suivez-nous</h4>
                <div class="social-icons">
                    <a href="https://www.facebook.com/PIGIERCIOFFICIEL/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="Instagram: https://www.instagram.com/pigierciofficiel/ " target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/school/pigiercotedivoire/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; 2024 Blog Pigier. Tous droits réservés. | Conçu avec <i class="fas fa-heart"></i> par Malik.</p>
        </div>
    </div>
</footer>




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
  </body>
</html>

