document.addEventListener("DOMContentLoaded", function () {
    const extensionsPerm = ["jpg", "jpeg", "png", "pdf"];
    const inputFiles = document.querySelectorAll("input[type='file']");

    inputFiles.forEach(input => {
        input.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const extensionFile = file.name.split(".").pop().toLowerCase();
                if (!extensionsPerm.includes(extensionFile)) {
                    alert("Seuls les fichiers JPG, PNG et PDF sont autorisés.");
                    this.value = ""; // réinitialiser l'input qu'on a sélectionné.
                }
            }
        });
    });
});
function toggleMatricule() {
    const nouveau = document.getElementById('nouveau');
    const renouveau = document.getElementById('renouveau');
    const carteContainer = document.getElementById('carte-container');
    const carteInput = document.getElementById('carte');
    if (renouveau.checked) {
        carteContainer.classList.remove('hidden');
        carteInput.required = true;
        nouveau.checked = false;
    } else {
        carteContainer.classList.add('hidden');
        carteInput.required = false;
        carteInput.value = ""; // vider le champ pour éviter d'envoyer un fichier vide
    }
}
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("form").addEventListener("submit", function (event) {
        let email = document.getElementById("email").value;
        let regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!regexEmail.test(email)) {
            alert("Veuillez entrer une adresse email valide !");
            event.preventDefault(); // Empêche l'envoi du formulaire si l'email est invalide
        }
    });
});
