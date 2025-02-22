<?php
require_once __DIR__ . '/config/config.php';
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
            color: #004494;
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
            color: #004494;
            margin-bottom: 1.5rem;
        }

        .sidebar-card {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .sidebar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .sidebar-card h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #004494;
            margin-bottom: 0.5rem;
        }

        .sidebar-card p {
            font-size: 0.9rem;
            color: #6c757d;
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
                order: -1;
            }
        }
    </style>
</head>
<body>
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
                    <a class="nav-link text-dark" href="article.php">Articles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#">À propos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#">Contact</a>
                </li>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Utilisateur connecté : Affichage du profil -->
                <div class="dropdown ms-3">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown"
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
        <a href="#" target="_blank" class="facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" target="_blank" class="instagram">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="#" target="_blank" class="linkedin">
            <i class="fab fa-linkedin-in"></i>
        </a>
    </div>
<style>.social-links {
    display: flex;
    gap: 10px; /* Espace entre les boutons */
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.3s, transform 0.3s;
}

.social-links a i {
    margin-right: 8px; /* Espace entre l'icône et le texte */
}

/* Couleurs spécifiques pour chaque réseau social */
.social-links a.facebook {
    background-color: #1877f2; /* Bleu Facebook */
}

.social-links a.instagram {
    background: linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d);
    color: white;
}

.social-links a.instagram:hover {
    background: linear-gradient(45deg, #344abf, #4a43c0, #6f2f9e, #a82a6f, #c8235c, #e61414);
}

.social-links a.linkedin {
    background-color: #0077b5; /* Bleu LinkedIn */
}

/* Effets au survol */
.social-links a:hover {
    transform: translateY(-2px); /* Légère élévation */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.social-links a.facebook:hover {
    background-color: #166fe5; /* Bleu Facebook plus foncé */
}



.social-links a.linkedin:hover {
    background-color: #006097; /* Bleu LinkedIn plus foncé */
}</style>
            <!-- Publicité ou article récent -->
            <div class="sidebar-card">
                <img src="img/hero-bg.jpg" alt="Publicité" />
                <h4>Publicité</h4>
                <p>Découvrez nos offres spéciales pour les enseignants.</p>
            </div>

            <!-- Article récemment lu -->
            <div class="sidebar-card">
                <img src="img/hero-bg.jpg" alt="Article Récent" />
                <h4>Article Récent</h4>
                <p>L'avenir de l'éducation en ligne.</p>
            </div>

           
        </div>
    </div>


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
    <script src="assets/js/script.js"></script>
</body>
</html>