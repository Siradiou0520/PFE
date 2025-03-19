function toggleMatricule() {
    const etudiant = document.getElementById('etudiant');
    const agent = document.getElementById('agent');
    const matriculeContainer = document.getElementById('matricule-container');
    
    if (agent.checked) {
        matriculeContainer.classList.remove('hidden');
        etudiant.checked = false;
    } else {
        matriculeContainer.classList.add('hidden');
    }
}

function validatePassword() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const errorMessage = document.getElementById('error-message');
    
    if (password !== confirm) {
        errorMessage.style.display = 'block';
        return false;
    } else {
        errorMessage.style.display = 'none';
        return true;
    }
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const errorMessage = document.getElementById('error-message');
    
    if (password !== confirm) {
        errorMessage.style.display = 'block';
    } else {
        errorMessage.style.display = 'none';
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
