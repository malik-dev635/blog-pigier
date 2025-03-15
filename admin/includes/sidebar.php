 <!-- Sidebar -->
 
 <div class="sidebar">
 <div class="sidebar-header">
                <div class="user-profile">
                    <img src="../<?php echo htmlspecialchars($current_user['profile_picture'] ?? 'img/default-avatar.png'); ?>" 
                         alt="Photo de profil" 
                         class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($current_user['username']); ?></span>
                        [<span class="user-role"><?php echo ucfirst(htmlspecialchars($current_user['role'])); ?></span>]
                    </div>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li>
                    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="articles.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'articles.php' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i>
                        <span>Articles</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="role_requests.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'role_requests.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Attribution de rôle</span>
                    </a>
                </li>
                <li>
                    <a href="comments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'comments.php' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span>Commentaires</span>
                    </a>
                </li>
                <li>
                    <a href="badges.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'badges.php' ? 'active' : ''; ?>">
                        <i class="fas fa-award"></i>
                        <span>Badges</span>
                    </a>
                </li>
                <li>
                    <a href="manage_events.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_events.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Événements</span>
                    </a>
                </li>
                <li>
                    <a href="../index.php">
                        <i class="fas fa-eye"></i>
                        <span>Voir le site</span>
                    </a>
                </li>
                <li>
                    <a href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>