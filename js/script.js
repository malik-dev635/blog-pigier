document.addEventListener("DOMContentLoaded", function () {
  let successModal = new bootstrap.Modal(
    document.getElementById("successModal")
  );
  successModal.show();

  // Fermer la modal après 3 secondes
  setTimeout(() => {
    successModal.hide();
  }, 3000);
});
