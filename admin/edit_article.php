<?php
require_once __DIR__ . '/../config/config.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'editor'])) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}


// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer l'ID de l'article à éditer
$article_id = $_GET['id'] ?? null;

if (!$article_id) {
    die("ID de l'article non spécifié.");
}

// Récupérer les informations de l'article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article non trouvé.");
}

// Vérifier si l'utilisateur est l'auteur de l'article ou un administrateur
if ($article['author_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    // Rediriger vers la page précédente avec un message d'erreur
    $_SESSION['error'] = "Vous n'avez pas l'autorisation de modifier cet article.";
    header("Location: articles.php");
    exit;
}

// Récupérer toutes les catégories disponibles
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la catégorie sélectionnée pour cet article
$selected_category = $pdo->prepare("SELECT category_id FROM article_category WHERE article_id = ?");
$selected_category->execute([$article_id]);
$selected_category_id = $selected_category->fetchColumn();

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image'];

    // Mettre à jour l'article
    $update_stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ? WHERE id = ?");
    $update_stmt->execute([$title, $content, $article_id]);

    // Mettre à jour la catégorie de l'article
    $update_category_stmt = $pdo->prepare("UPDATE article_category SET category_id = ? WHERE article_id = ?");
    $update_category_stmt->execute([$category_id, $article_id]);

    // Gestion de l'image de couverture
    if ($image['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        $image_name = basename($image['name']);
        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Sécuriser le nom du fichier en utilisant uniqid et vérifier l'extension
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($image_extension, $valid_extensions)) {
            $image_name = uniqid('img_', true) . '.' . $image_extension; // Nouveau nom sécurisé pour l'image
            $image_path = $uploadDir . $image_name;

            // Déplacer le fichier téléchargé
            if (move_uploaded_file($image['tmp_name'], $image_path)) {
                // Mettre à jour le chemin de l'image dans la base de données
                $update_image_stmt = $pdo->prepare("UPDATE articles SET image = ? WHERE id = ?");
                $update_image_stmt->execute(['uploads/' . $image_name, $article_id]);
            } else {
                die("Erreur lors du téléchargement de l'image.");
            }
        } else {
            die("Extension d'image non autorisée.");
        }
    }

    // Rediriger vers la page de l'article ou une autre page
    header("Location: dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="shortcut icon" href="../img/logo.png" />
    <script src="https://cdn.tiny.cloud/1/epzhe26otp0t6yw6h0yg6940cawdvt1uklclgy6sxrxezg2v/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8f9fa;
        }

        .form-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: 2rem auto;
        }

        .form-container h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #004494;
            margin-bottom: 1.5rem;
        }

        .form-control {
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: #004494;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #003366;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-edit me-2"></i>Modifier l'article</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                </div>
            </div>

            <!-- Article Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informations de l'article</h2>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-4">
                                    <label for="title" class="form-label">Titre</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="content" class="form-label">Contenu</label>
                                    <textarea class="form-control" id="content" name="content" rows="12" required><?= htmlspecialchars($article['content']) ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Paramètres de publication</h5>
                                        
                                        <div class="form-group mb-4">
                                            <label for="category_id" class="form-label">Catégorie</label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $selected_category_id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="image" class="form-label">Image de couverture</label>
                                            <?php if ($article['image']): ?>
                                                <div class="image-preview mb-3">
                                                    <img src="../<?= $article['image'] ?>" alt="Image actuelle" class="img-fluid rounded">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="image" name="image">
                                            <small class="text-muted">
                                                Format recommandé : JPG, PNG. Taille max : 2MB
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>
                                        Mettre à jour l'article
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
  // Initialisation de TinyMCE
  tinymce.init({
    selector: '#content',
    height: 600,
    plugins: [
      'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 
      'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount'
    ],
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' + 
             'link image media table | align lineheight | checklist numlist bullist indent outdent | ' + 
             'emoticons charmap | removeformat',
});


  // Vérifier si le formulaire existe avant d'ajouter l'écouteur d'événement
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector('form');

    if (form) {
      form.addEventListener('submit', function (e) {
        if (typeof tinymce !== 'undefined') {
          // Sauvegarder le contenu de TinyMCE dans le textarea avant l'envoi
          tinymce.triggerSave();
        } else {
          console.warn("TinyMCE n'est pas chargé.");
        }

        // Supprimez cette ligne en production si vous souhaitez que le formulaire se soumette réellement
        // e.preventDefault();  
        console.log("Formulaire soumis avec le contenu TinyMCE.");
      });
    } else {
      console.warn("Aucun formulaire trouvé.");
    }
  });
</script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
