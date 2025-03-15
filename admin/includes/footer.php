<!-- Footer -->
<footer class="footer mt-auto py-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Dashboard Pigier Yamoussoukro. Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted">Version 1.0 | <i class="fas fa-code text-primary"></i> Malik</p>
            </div>
        </div>
    </div>
</footer>

<script>
    // Ajuster la position du footer en fonction de la hauteur du contenu
    document.addEventListener('DOMContentLoaded', function() {
        const mainContent = document.querySelector('.main-content');
        const footer = document.querySelector('.footer');
        const sidebar = document.querySelector('.sidebar');
        
        if (mainContent && footer) {
            // Fonction pour ajuster le footer
            function adjustFooter() {
                // Calculer la largeur disponible pour le footer (en tenant compte de la sidebar)
                if (sidebar && window.innerWidth > 768) {
                    const sidebarWidth = sidebar.offsetWidth;
                    footer.style.marginLeft = sidebarWidth + 'px';
                    footer.style.width = `calc(100% - ${sidebarWidth}px)`;
                } else {
                    footer.style.marginLeft = '0';
                    footer.style.width = '100%';
                }
                
                // Ajuster la position verticale
                if (mainContent.offsetHeight + footer.offsetHeight < window.innerHeight) {
                    footer.classList.add('fixed-bottom');
                } else {
                    footer.classList.remove('fixed-bottom');
                }
            }
            
            // Ajuster au chargement
            adjustFooter();
            
            // Ajuster lors du redimensionnement de la fenêtre
            window.addEventListener('resize', adjustFooter);
        }
    });
</script>

<style>
    .footer {
        background-color: #fff;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        z-index: 1000;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }
    
    .footer.fixed-bottom {
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }
    
    @media (max-width: 768px) {
        .footer {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

