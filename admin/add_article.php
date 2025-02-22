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

// Récupérer les catégories depuis la base de données
try {
    $categoriesQuery = "SELECT * FROM categories";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sécuriser les entrées utilisateur
    $title = htmlspecialchars(trim($_POST['title']));
    $content = trim($_POST['content']);
    $category_id = (int) $_POST['category_id']; // Assurer un entier
    $author_id = $_SESSION['user_id']; 

    // Vérifier si tous les champs sont remplis
    if (empty($title) || empty($content) || empty($category_id)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        header('Location: dashboard.php');
        exit();
    }

    // Gestion de l'upload de l'image
    $image = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Sécuriser le nom de fichier
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $imageName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $imageName); // Nettoyage du nom
        $imagePath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = 'uploads/' . $imageName;
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'upload de l'image.";
            header('Location: dashboard.php');
            exit();
        }
    }

    try {
        // Insertion de l'article
        $query = "INSERT INTO articles (title, content, image, author_id) VALUES (:title, :content, :image, :author_id)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':image' => $image,
            ':author_id' => $author_id,
        ]);

        // Récupérer l'ID de l'article inséré
        $article_id = $pdo->lastInsertId();

        // Associer l'article à sa catégorie
        $linkQuery = "INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)";
        $linkStmt = $pdo->prepare($linkQuery);
        $linkStmt->execute([
            ':article_id' => $article_id,
            ':category_id' => $category_id,
        ]);

        // Redirection avec message de succès
        $_SESSION['success_message'] = "L'article a été ajouté avec succès !";
        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur SQL : " . $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ajouter un article | Blog Pigier</title>
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
<div class="form-container">
    <h1>Ajouter un nouvel article</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Contenu</label>
            <textarea class="form-control" id="content" name="content" rows="10"></textarea>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">Catégorie</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image de couverture</label>
            <input type="file" class="form-control" id="image" name="image" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Publier l'article</button>
    </form>
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


<!-- Script Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>