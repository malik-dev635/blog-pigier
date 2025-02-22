<?php
require_once __DIR__ . '/../config/config.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'utilisateur est connecté et a les droits admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

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
    header("Location: ../article.php?id=$article_id");
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
    <div class="container mt-5">
        <div class="form-container">
            <h1>Modifier l'article</h1>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Contenu</label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($article['content']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Catégorie</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category['id'] == $selected_category_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image de couverture</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <?php if ($article['image']): ?>
                        <p>Image actuelle : <img src="../<?= $article['image'] ?>" alt="Image de couverture" width="400"></p>
                    <?php endif; ?>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Mettre à jour l'article</button>
            </form>
        </div>
    </div>

    <script>
  // Initialisation de TinyMCE
  tinymce.init({
    selector: '#content',
    height: 600,
    plugins: [
      'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 
      'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
      'checklist', 'mediaembed', 'casechange', 'export', 'formatpainter', 'pageembed', 
      'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 
      'advcode', 'editimage', 'advtemplate', 'ai', 'mentions', 'tinycomments', 
      'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 
      'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'
    ],
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' + 
             'link image media table mergetags | addcomment showcomments | spellcheckdialog ' + 
             'a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | ' + 
             'emoticons charmap | removeformat',
    tinycomments_mode: 'embedded',
    tinycomments_author: 'Author name',
    mergetags_list: [
      { value: 'First.Name', title: 'First Name' },
      { value: 'Email', title: 'Email' }
    ]
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
