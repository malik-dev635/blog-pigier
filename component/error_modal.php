<?php if (isset($_SESSION['error_message'])): ?>
<!-- Bootstrap Modal -->
<div class="modal fade show d-block" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-danger shadow-lg animate__animated animate__shakeX">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger fw-bold" id="errorModalLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i> Erreur Fatale !
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-emoji-dizzy display-1 text-danger animate__animated animate__pulse animate__infinite"></i>
                <p class="mt-3 fs-5"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
            <div class="modal-footer border-danger d-flex justify-content-center">
                <button type="button" class="btn btn-danger fw-bold px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Effet sonore flippant -->
<audio id="errorSound" autoplay>
    <source src="sounds/error.mp3" type="audio/mp3">
</audio>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let sound = document.getElementById("errorSound");
        sound.volume = 0.6;
        sound.play();
    });
</script>

<?php unset($_SESSION['error_message']); // Supprime le message après affichage ?>
<?php endif; ?>
