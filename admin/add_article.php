<?php
require_once __DIR__ . '/../config/config.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'utilisateur est connecté et a les droits admin ou editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $title = htmlspecialchars(trim($_POST['title']), ENT_NOQUOTES);
    $content = trim($_POST['content']);
    $category_id = (int) $_POST['category_id']; 
    $author_id = $_SESSION['user_id'];

    // Vérifier si tous les champs sont remplis
    if (empty($title) || empty($content) || empty($category_id)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        header('Location: add_article.php');
        exit();
    }
    
    // Vérifier la longueur du titre
    if (mb_strlen($title) > 50) {
        $_SESSION['error_message'] = "Le titre ne doit pas dépasser 50 caractères.";
        header('Location: add_article.php');
        exit();
    }

    // Gestion de l'upload de l'image
    $image = null;
    $max_file_size = 2 * 1024 * 1024; // 2MB en octets
    
    if (!empty($_FILES['image']['name'])) {
        // Vérifier les erreurs d'upload
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error_message = "Erreur lors de l'upload de l'image: ";
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message .= "L'image est trop volumineuse (max: 2MB).";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message .= "L'upload a été interrompu.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message .= "Aucun fichier n'a été téléchargé.";
                    break;
                default:
                    $error_message .= "Erreur inconnue.";
            }
            $_SESSION['error_message'] = $error_message;
            header('Location: add_article.php');
            exit();
        }
        
        // Vérifier la taille du fichier manuellement
        if ($_FILES['image']['size'] > $max_file_size) {
            $_SESSION['error_message'] = "L'image est trop volumineuse. La taille maximale autorisée est de 2MB.";
            header('Location: add_article.php');
            exit();
        }
        
        // Vérifier le type de fichier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            $_SESSION['error_message'] = "Type de fichier non autorisé. Seuls les formats JPG, PNG, GIF et WEBP sont acceptés.";
            header('Location: add_article.php');
            exit();
        }
        
        // Procéder à l'upload
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
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement de l'image.";
            header('Location: add_article.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Veuillez sélectionner une image pour l'article.";
        header('Location: add_article.php');
        exit();
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
        header('Location: add_article.php');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../img/logo.png" />
    <script src="https://cdn.tiny.cloud/1/epzhe26otp0t6yw6h0yg6940cawdvt1uklclgy6sxrxezg2v/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-plus-circle me-2"></i>Nouvel Article</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

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
                                    <label for="title" class="form-label">Titre de l'article</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="title" name="title" required maxlength="50" 
                                               oninput="document.getElementById('charCount').textContent = this.value.length + '/50'">
                                        <span class="input-group-text" id="charCount">0/50</span>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i> Maximum 50 caractères
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez saisir un titre pour l'article.
                                    </div>
        </div>

                                <div class="form-group mb-4">
            <label for="content" class="form-label">Contenu</label>
                                    <textarea class="form-control" id="content" name="content" rows="12" required></textarea>
                                    <div class="invalid-feedback">
                                        Le contenu de l'article est requis.
                                    </div>
                                </div>
        </div>

                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Paramètres de publication</h5>
                                        
                                        <div class="form-group mb-4">
            <label for="category_id" class="form-label">Catégorie</label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Sélectionner une catégorie</option>
                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>">
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                <?php endforeach; ?>
            </select>
                                            <div class="invalid-feedback">
                                                Veuillez sélectionner une catégorie.
                                            </div>
        </div>

                                        <div class="form-group mb-4">
            <label for="image" class="form-label">Image de couverture</label>
                                            <div class="image-preview mb-3 d-none">
                                                <img id="preview" src="#" alt="Aperçu" class="img-fluid rounded">
                                            </div>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                                            <div class="invalid-feedback">
                                                Veuillez sélectionner une image de couverture.
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i> Formats acceptés : JPG, PNG, GIF, WEBP
                                                <br>
                                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i> Taille maximale : <strong>2MB</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Publier l'article
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
<?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // TinyMCE Configuration
  tinymce.init({
    selector: '#content',
            height: 500,
            menubar: true,
    plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: "Poppins", sans-serif; font-size: 16px }'
        });

        // Compteur de caractères pour le titre
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const charCount = document.getElementById('charCount');
            
            // Initialiser le compteur
            charCount.textContent = '0/50';
            
            // Mettre à jour le compteur lors de la saisie
            titleInput.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count + '/50';
                
                // Changer la couleur si on approche de la limite
                if (count > 40) {
                    charCount.classList.add('text-warning');
        } else {
                    charCount.classList.remove('text-warning');
                }
                
                // Changer la couleur si on atteint la limite
                if (count >= 50) {
                    charCount.classList.add('text-danger');
    } else {
                    charCount.classList.remove('text-danger');
                }
            });
        });

        // Image Preview et validation
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const previewContainer = document.querySelector('.image-preview');
            const maxFileSize = 2 * 1024 * 1024; // 2MB en octets
            const fileInput = e.target;
            
            // Vérifier si un fichier a été sélectionné
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                
                // Vérifier la taille du fichier
                if (file.size > maxFileSize) {
                    alert('L\'image est trop volumineuse. La taille maximale autorisée est de 2MB.');
                    fileInput.value = ''; // Réinitialiser l'input
                    previewContainer.classList.add('d-none');
                    return;
                }
                
                // Vérifier le type de fichier
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Type de fichier non autorisé. Seuls les formats JPG, PNG, GIF et WEBP sont acceptés.');
                    fileInput.value = ''; // Réinitialiser l'input
                    previewContainer.classList.add('d-none');
                    return;
                }
                
                // Afficher l'aperçu
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Form Validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>