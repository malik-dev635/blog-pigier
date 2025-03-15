<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

// Créer la table role_requests
try {
    $sql = "CREATE TABLE IF NOT EXISTS role_requests (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        requested_role ENUM('editor', 'admin') NOT NULL,
        reason TEXT NOT NULL,
        experience TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    
    $pdo->exec($sql);
} catch (PDOException $e) {
    die("Erreur lors de la création de la table : " . $e->getMessage());
}

// Récupérer les informations de l'utilisateur connecté
$user_query = "SELECT username, profile_picture, role FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement des actions (approuver/rejeter)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    
    try {
        // Récupérer les informations de la demande
        $request_query = "SELECT user_id, requested_role FROM role_requests WHERE id = ?";
        $stmt = $pdo->prepare($request_query);
        $stmt->execute([$request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            if ($action === 'approve') {
                // Mettre à jour le rôle de l'utilisateur
                $update_user = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $update_user->execute([$request['requested_role'], $request['user_id']]);
                
                // Mettre à jour le statut de la demande
                $update_request = $pdo->prepare("UPDATE role_requests SET status = 'approved', updated_at = NOW() WHERE id = ?");
                $update_request->execute([$request_id]);
                
                $_SESSION['success_message'] = "La demande a été approuvée et le rôle de l'utilisateur a été mis à jour.";
            } elseif ($action === 'reject') {
                // Mettre à jour le statut de la demande
                $update_request = $pdo->prepare("UPDATE role_requests SET status = 'rejected', updated_at = NOW() WHERE id = ?");
                $update_request->execute([$request_id]);
                
                $_SESSION['success_message'] = "La demande a été rejetée.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
    }
    
    header("Location: role_requests.php");
    exit;
}

// Récupérer toutes les demandes avec les informations des utilisateurs
try {
    $sql = "SELECT r.*, u.username, u.email, u.profile_picture 
            FROM role_requests r 
            JOIN users u ON r.user_id = u.id 
            ORDER BY 
                CASE r.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'approved' THEN 2 
                    ELSE 3 
                END,
                r.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Demandes de Rôle | Blog Pigier</title>
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
            <div class="header">
                <h1><i class="fas fa-user-tag me-2"></i>Gestion des Demandes de Rôle</h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Requests Table Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Liste des Demandes</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Rôle demandé</th>
                                <th>Motif</th>
                                <th>Expérience</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../<?= htmlspecialchars($request['profile_picture'] ?? 'img/default-avatar.png') ?>" 
                                                 alt="Avatar" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                            <div>
                                                <span class="fw-bold"><?= htmlspecialchars($request['username']) ?></span><br>
                                                <small class="text-muted"><?= htmlspecialchars($request['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= ucfirst(htmlspecialchars($request['requested_role'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#reasonModal<?= $request['id'] ?>">
                                            Voir le motif
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#experienceModal<?= $request['id'] ?>">
                                            Voir l'expérience
                                        </button>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'bg-warning',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger'
                                        ];
                                        $statusText = [
                                            'pending' => 'En attente',
                                            'approved' => 'Approuvée',
                                            'rejected' => 'Rejetée'
                                        ];
                                        ?>
                                        <span class="badge <?= $statusClass[$request['status']] ?>">
                                            <?= $statusText[$request['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <div class="d-flex gap-2">
                                                <form action="role_requests.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir approuver cette demande ?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="role_requests.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Modal pour le motif -->
                                <div class="modal fade" id="reasonModal<?= $request['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Motif de la demande</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?= nl2br(htmlspecialchars($request['reason'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal pour l'expérience -->
                                <div class="modal fade" id="experienceModal<?= $request['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Expérience</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?= nl2br(htmlspecialchars($request['experience'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 