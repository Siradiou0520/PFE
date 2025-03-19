// scripts.js
document.getElementById("upload-form").addEventListener("submit", function(event) {
    event.preventDefault(); // Empêcher le rechargement de la page

    // Récupérer l'élément de statut
    const statusElement = document.getElementById("upload-status");

    // Vérifier si un fichier a été sélectionné
    const fileInput = document.getElementById("file-upload");
    if (fileInput.files.length > 0) {
        // Si un fichier est sélectionné, afficher un message de succès
        statusElement.textContent = "Fichier téléchargé avec succès !";
        statusElement.style.color = "green";
    } else {
        // Si aucun fichier n'est sélectionné, afficher un message d'erreur
        statusElement.textContent = "Veuillez sélectionner un fichier.";
        statusElement.style.color = "red";
    }
});

// Fonction pour sélectionner "Première demande"
function selectFirstTime() {
    document.getElementById("request-status").textContent = "Vous avez sélectionné 'Première demande'.";
}

// Fonction pour sélectionner "Renouvellement"
function selectRenewal() {
    document.getElementById("request-status").textContent = "Vous avez sélectionné 'Renouvellement'.";
}
