<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/loader.php';
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blog Pigier</title>
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
                        <a class="nav-link text-dark " href="search-article.php">Articles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="about.php">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark active" href="contact.php">Contact</a>
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
   
</section>

    <!-- Footer -->
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
                        <a href="https://www.instagram.com/pigierciofficiel/" target="_blank"><i class="fab fa-instagram"></i></a>
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

    <!-- Scripts -->
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