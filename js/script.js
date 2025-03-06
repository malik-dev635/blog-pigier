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

// Gestion de l'affichage de la barre de recherche
document.getElementById("searchToggle").addEventListener("click", function () {
  const searchBar = document.getElementById("searchBar");
  searchBar.classList.toggle("active");
  if (searchBar.classList.contains("active")) {
    document.getElementById("searchInput").focus();
  }
});

document.getElementById("closeSearch").addEventListener("click", function () {
  const searchBar = document.getElementById("searchBar");
  searchBar.classList.remove("active");
  document.getElementById("searchInput").value = ""; // Réinitialiser la recherche
});

// Gestion de la touche "Entrée" pour soumettre le formulaire
document
  .getElementById("searchInput")
  .addEventListener("keypress", function (event) {
    if (event.keyCode === 13) {
      event.preventDefault(); // Empêche le comportement par défaut
      document.querySelector("form[action='search-article.php']").submit(); // Soumet le formulaire
    }
  });
