<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/loader.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}


// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur introuvable.";
        header("Location: logout.php");
        exit;
    }

    // Récupérer le nombre d'articles publiés
    $stmt = $pdo->prepare("SELECT COUNT(*) as article_count FROM articles WHERE author_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $article_count = $stmt->fetch(PDO::FETCH_ASSOC)['article_count'];

    // Récupérer les articles de l'utilisateur
    $stmt = $pdo->prepare("SELECT id, title, image, created_at FROM articles WHERE author_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blog Pigier - Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
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
    <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
/>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="shortcut icon" href="img/logo.png" />
    <style>
      /* Styles spécifiques au profil */
.profile-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: #f4f4f4;
}

.profile-card {
    width: 380px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.profile-header {
    position: relative;
}

.profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #3498db;
    margin-bottom: 10px;
}

h2 {
    margin: 10px 0;
    font-size: 22px;
    color: #333;
}

.email {
    font-size: 14px;
    color: #777;
}

.profile-body {
    margin: 20px 0;
}

.profile-footer {
    display: flex;
    justify-content: space-between;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    transition: 0.3s;
}


.btn-logout {
    background: #e74c3c;
    color: white;
}

.btn-logout:hover {
    background: #c0392b;
}

.profile-picture-container {
  position: relative;
  display: inline-block;
}

.profile-picture-container i {
  position: absolute;
  top: 50px;
  left: 50px;
  background-color: #004494;
  padding: 8px;
  border-radius: 50%;
  color: white;
  font-size: 14px;
  cursor: pointer;
}

.profile-picture-container:hover i {
  background-color: #2980b9;
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
                    <a class="nav-link text-dark" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="search-article.php">Articles</a>
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

    <div class="container mt-4 mb-4">
      <div class="row">
        <div class="col-md-4">
          <div class="card p-4 shadow-sm">
            <h4 class="text-center">Mon Profil</h4>
            <div class="text-center">
              <img src="<?php echo isset($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'img/default-profile.png'; ?>" class="rounded-circle" style="width: 120px; height: 120px;">
              <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user['username']); ?></p>
              <p><strong>Email : </strong> <?= htmlspecialchars($user['email']); ?></p>
              <p><strong>Inscrit le :</strong> <?= date("d/m/Y", strtotime($user['created_at'])); ?></p>
              <p><strong>Articles publiés :</strong> <?= $article_count; ?></p>
              <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-user-edit me-2"></i>Modifier le profil
                </button>
                <?php if ($_SESSION['role'] === 'user'): ?>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#roleRequestModal">
                        <i class="fas fa-user-tag me-2"></i>Demander un rôle
                    </button>
                <?php endif; ?>
                <a href="auth/logout.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor')): ?>
    <div class="col-md-8">
        <div class="card p-4 shadow-sm">
            <h4 class="text-center">Mes Articles</h4>
            <?php if (count($articles) > 0): ?>
                <div class="list-group">
                    <?php foreach ($articles as $article): ?>
                        <a href="article.php?id=<?= $article['id']; ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <img src="<?= htmlspecialchars($article['image'] ?? 'img/default-article.jpg'); ?>" class="me-3 rounded" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0"> <?= htmlspecialchars($article['title']); ?> </h6>
                                <small class="text-muted">Publié le <?= date("d/m/Y", strtotime($article['created_at'])); ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">Aucun article publié.</p>
            <?php endif; ?>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
        </div>
      </div>
    </div>
    <?php include 'component/footer.php' ?>

 


<!-- Modal pour modifier le profil -->


<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Modifier le profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editProfileForm" action="update_profile.php" method="POST">
          <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
          </div>
          <div class="mb-3">
          <div class="profile-picture-container text-center position-relative" style="cursor: pointer;">
  <img src="<?php echo isset($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'img/default-profile.png'; ?>" 
       class="rounded-circle" 
       style="width: 120px; height: 120px; object-fit: cover;" 
       id="profilePicture">
  <div class="position-absolute top-0 start-50 translate-middle rounded-circle p-2" 
       style="transform: translateX(-50%);">
    <i class="fas fa-pencil text-white"></i>
  </div>
</div>
<input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
          </div>
        </form>
      </div>
    </div>
  </div><script>
  // Gérer le clic sur l'image de profil
  document.querySelector('.profile-picture-container').addEventListener('click', function () {
    document.getElementById('profilePictureInput').click();
  });

  // Gérer la sélection d'un fichier
  document.getElementById('profilePictureInput').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        // Afficher l'aperçu de l'image
        document.getElementById('profilePicture').src = e.target.result;

        // Envoyer l'image au serveur (via AJAX ou formulaire)
        uploadProfilePicture(file);
      };
      reader.readAsDataURL(file);
    }
  });

  // Fonction pour envoyer l'image au serveur
  function uploadProfilePicture(file) {
    const formData = new FormData();
    formData.append('profile_picture', file);

    fetch('update_profile_picture.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .catch(error => {
      console.error('Erreur :', error);
    });
  }
</script>
</div>

<!-- Modal Demande de Rôle -->
<div class="modal fade" id="roleRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-tag me-2"></i>Demande de changement de rôle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_role_request.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="requested_role" class="form-label">Rôle souhaité</label>
                        <select class="form-select" id="requested_role" name="requested_role" required>
                            <option value="editor">Éditeur</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Motif de la demande</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Expliquez pourquoi vous souhaitez devenir éditeur..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="experience" class="form-label">Expérience pertinente</label>
                        <textarea class="form-control" id="experience" name="experience" rows="4" required placeholder="Décrivez votre expérience en rédaction ou édition..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
