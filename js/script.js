/*document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    if (email && password) {
        alert("Connexion réussie");
    }
});*/
const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        document.getElementById('errorMessage').style.display = 'block';
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
    