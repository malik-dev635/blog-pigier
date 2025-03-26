<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/loader.php';
session_start();


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

// Récupérer les statistiques
// 1. Nombre total d'articles
$articles_query = "SELECT COUNT(*) as total FROM articles";
$stmt = $pdo->prepare($articles_query);
$stmt->execute();
$total_articles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 2. Nombre total d'utilisateurs
$users_query = "SELECT COUNT(*) as total FROM users";
$stmt = $pdo->prepare($users_query);
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 3. Nombre total de commentaires
$comments_query = "SELECT COUNT(*) as total FROM comments";
$stmt = $pdo->prepare($comments_query);
$stmt->execute();
$total_comments = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 4. Nombre total de catégories
$categories_query = "SELECT COUNT(*) as total FROM categories";
$stmt = $pdo->prepare($categories_query);
$stmt->execute();
$total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 5. Nombre total d'événements
$events_query = "SELECT COUNT(*) as total FROM events";
try {
    $stmt = $pdo->prepare($events_query);
    $stmt->execute();
    $total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} catch (PDOException $e) {
    $total_events = 0;
}

// Récupérer les derniers articles
$recent_articles_query = "SELECT a.id, a.title, a.created_at, u.username, u.profile_picture 
                         FROM articles a 
                         JOIN users u ON a.author_id = u.id 
                         ORDER BY a.created_at DESC 
                         LIMIT 5";
$stmt = $pdo->prepare($recent_articles_query);
$stmt->execute();
$recent_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les derniers utilisateurs inscrits
$recent_users_query = "SELECT id, username, profile_picture, role, created_at 
                      FROM users 
                      ORDER BY created_at DESC 
                      LIMIT 5";
$stmt = $pdo->prepare($recent_users_query);
$stmt->execute();
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les prochains événements
$upcoming_events_query = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3";
try {
    $stmt = $pdo->prepare($upcoming_events_query);
    $stmt->execute();
    $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $upcoming_events = [];
}

// Gestion des messages de succès
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Blog Pigier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../img/logo.png" />
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">

            <!-- Header -->
            <div class="header mb-4">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div>
                        <h1 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord</h1>
                        <p class="text-muted mb-0">Bienvenue, <?php echo htmlspecialchars($current_user['username']); ?></p>
                    </div>
                    <div>
                        <a href="../index.php" target="_blank" class="btn btn-site">
                            <i class="fas fa-eye me-1"></i>
                            Voir le site
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-4 col-xl">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="stat-details ms-3">
                                <h3 class="stat-number"><?php echo $total_articles; ?></h3>
                                <p class="stat-label">Articles</p>
                            </div>
                            <div class="ms-auto">
                                <a href="articles.php" class="btn btn-sm btn-link text-primary p-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 col-xl">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-details ms-3">
                                <h3 class="stat-number"><?php echo $total_users; ?></h3>
                                <p class="stat-label">Utilisateurs</p>
                            </div>
                            <div class="ms-auto">
                                <a href="users.php" class="btn btn-sm btn-link text-success p-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 col-xl">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="stat-details ms-3">
                                <h3 class="stat-number"><?php echo $total_comments; ?></h3>
                                <p class="stat-label">Commentaires</p>
                            </div>
                            <div class="ms-auto">
                                <a href="comments.php" class="btn btn-sm btn-link text-info p-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 col-xl">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-details ms-3">
                                <h3 class="stat-number"><?php echo $total_categories; ?></h3>
                                <p class="stat-label">Catégories</p>
                            </div>
                            <div class="ms-auto">
                                <a href="categories.php" class="btn btn-sm btn-link text-warning p-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 col-xl">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="stat-icon bg-danger">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-details ms-3">
                                <h3 class="stat-number"><?php echo $total_events; ?></h3>
                                <p class="stat-label">Événements</p>
                            </div>
                            <div class="ms-auto">
                                <a href="manage_events.php" class="btn btn-sm btn-link text-danger p-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row g-3">
                <!-- Recent Articles -->
                <div class="col-lg-6 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h5 class="card-title mb-0"><i class="fas fa-newspaper me-2 text-primary"></i>Articles Récents</h5>
                            <a href="articles.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Ajouter
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if (empty($recent_articles)): ?>
                                    <div class="list-group-item text-center py-4">
                                        <p class="text-muted mb-0">Aucun article n'a été publié.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_articles as $article): ?>
                                        <div class="list-group-item py-2 px-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img src="../<?php echo htmlspecialchars($article['profile_picture'] ?? 'img/default-avatar.png'); ?>" 
                                                         alt="Avatar" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                                </div>
                                                <div class="flex-grow-1 ms-2 overflow-hidden">
                                                    <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($article['title']); ?></h6>
                                                    <p class="text-muted small mb-0">
                                                        <span class="me-2"><?php echo htmlspecialchars($article['username']); ?></span>
                                                        <i class="far fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0 ms-2">
                                                    <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="col-lg-6 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h5 class="card-title mb-0"><i class="fas fa-users me-2 text-success"></i>Utilisateurs Récents</h5>
                            <a href="users.php" class="btn btn-sm btn-success">
                                <i class="fas fa-user-plus me-1"></i>Ajouter
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if (empty($recent_users)): ?>
                                    <div class="list-group-item text-center py-4">
                                        <p class="text-muted mb-0">Aucun utilisateur inscrit récemment.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_users as $user): ?>
                                        <div class="list-group-item py-2 px-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img src="../<?php echo htmlspecialchars($user['profile_picture'] ?? 'img/default-avatar.png'); ?>" 
                                                         alt="Avatar" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h6>
                                                    <p class="text-muted small mb-0">
                                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'editor' ? 'warning' : 'secondary'); ?> me-1">
                                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                        </span>
                                                        <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="row g-3">
                <div class="col-12 mb-3">
                    <div class="card dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h5 class="card-title mb-0"><i class="fas fa-calendar-alt me-2 text-danger"></i>Événements à Venir</h5>
                            <a href="manage_events.php" class="btn btn-sm btn-danger">
                                <i class="fas fa-plus me-1"></i>Ajouter
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($upcoming_events)): ?>
                                <div class="text-center py-3">
                                    <p class="text-muted mb-0">Aucun événement à venir n'est programmé.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($upcoming_events as $event): ?>
                                        <div class="col-md-4">
                                            <div class="event-card h-100">
                                                <div class="event-date">
                                                    <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                                    <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                                                </div>
                                                <div class="event-content">
                                                    <h5 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                                    <p class="event-details">
                                                        <i class="far fa-clock me-1 text-muted"></i><?php echo htmlspecialchars($event['time']); ?><br>
                                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i><?php echo htmlspecialchars($event['location']); ?>
                                                    </p>
                                                    <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 80)) . (strlen($event['description']) > 80 ? '...' : ''); ?></p>
                                                    <a href="manage_events.php" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-edit me-1"></i>Modifier
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-3">
                <div class="col-12 mb-3">
                    <div class="card dashboard-card">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0"><i class="fas fa-bolt me-2 text-warning"></i>Actions Rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <a href="add_article.php" class="quick-action-card">
                                        <div class="quick-action-icon bg-primary">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <span>Nouvel Article</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="categories.php" class="quick-action-card">
                                        <div class="quick-action-icon bg-warning">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <span>Gérer Catégories</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="manage_events.php" class="quick-action-card">
                                        <div class="quick-action-icon bg-danger">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <span>Nouvel Événement</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="badges.php" class="quick-action-card">
                                        <div class="quick-action-icon bg-info">
                                            <i class="fas fa-award"></i>
                                        </div>
                                        <span>Gérer Badges</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Styles généraux */
        body {
            font-size: 0.9rem;
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
  background-color: #f8f9fa;
}

        .main-content {
            flex: 1;
            padding: 1.25rem;
            overflow-y: auto;
        }
        
        .header {
            background-color: #fff;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        /* Style pour le bouton "Voir le site" */
        .btn-site {
            background-color: #004494;
            color: white;
            border: 2px solid #004494;
            border-radius: 25px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-site:hover {
            background-color: #003377;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Styles pour les cartes */
        .dashboard-card {
  border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        /* Styles pour les cartes de statistiques */
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon i {
            color: white;
            font-size: 1.2rem;
        }
        
        .stat-details {
            flex-grow: 1;
        }
        
        .stat-number {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.2;
        }
        
        .stat-label {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 0.8rem;
        }
        
        /* Styles pour les événements */
        .event-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
            transition: transform 0.2s ease;
            position: relative;
            padding: 1rem;
            height: 100%;
  display: flex;
            flex-direction: column;
        }
        
        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .event-date {
            background: #dc3545;
            color: white;
            text-align: center;
            padding: 0.5rem;
            border-radius: 0.5rem;
            display: inline-block;
            margin-bottom: 0.75rem;
            width: 60px;
        }
        
        .event-date .day {
            font-size: 1.25rem;
            font-weight: 700;
            display: block;
            line-height: 1;
        }
        
        .event-date .month {
            font-size: 0.75rem;
            text-transform: uppercase;
            display: block;
        }
        
        .event-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .event-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .event-details {
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .event-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.75rem;
            flex: 1;
        }
        
        /* Styles pour les actions rapides */
        .quick-action-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
  padding: 1rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
            color: #333;
            height: 100%;
            text-align: center;
        }
        
        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            color: #333;
        }
        
        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        
        .quick-action-icon i {
            color: white;
            font-size: 1.2rem;
        }
        
        .quick-action-card span {
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Optimisations pour écran 14 pouces */
        @media (max-width: 1366px) {
            .main-content {
                padding: 1rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            .list-group-item {
                padding: 0.5rem 0.75rem;
            }
            
            .stat-number {
                font-size: 1.1rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            .event-title {
                font-size: 0.95rem;
            }
            
            .event-details, .event-description {
                font-size: 0.8rem;
            }
            
            .quick-action-card {
                padding: 0.75rem;
            }
            
            .quick-action-icon {
                width: 35px;
                height: 35px;
            }
            
            .quick-action-card span {
                font-size: 0.8rem;
            }
        }
    </style>
</body>
</html>