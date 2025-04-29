<?php
session_start();

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost"; // Adresse du serveur MySQL
$username = "root"; // Nom d'utilisateur MySQL
$password = ""; // Mot de passe MySQL
$database = "smi6"; // Nom de la base

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}

// Définir l'encodage des caractères pour éviter les problèmes avec les accents
$conn->set_charset("utf8");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}

$etudiant_id = $_SESSION['user_id'];

// Récupérer les informations de l'étudiant
$sql_etudiant = "SELECT nom, prénom, email FROM etudiant  WHERE id = ?";
$stmt = $conn->prepare($sql_etudiant);

// Vérifier si la requête a été bien préparée
if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête étudiant : " . $conn->error);
}

$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$result = $stmt->get_result();
$etudiant = $result->fetch_assoc();

// Vérifier si un étudiant a été trouvé
if (!$etudiant) {
    die("Erreur : Étudiant non trouvé dans la base de données.");
}

// Récupérer les demandes de l'étudiant
$sql_demandes = "SELECT D.id, D.date_de_dépôt, D.statut FROM demande D INNER JOIN etudiant E ON E.id = D.etudiant_id WHERE D.etudiant_id = ? ORDER BY date_de_dépôt DESC";
$stmt = $conn->prepare($sql_demandes);

// Vérifier si la requête a été bien préparée
if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête des demandes : " . $conn->error);
}

$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$demandes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Vérifier si les données JSON sont bien générées
$etudiant_json = json_encode($etudiant);
$demandes_json = json_encode($demandes);

if (!$etudiant_json || !$demandes_json) {
    die("Erreur lors de l'encodage JSON.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tableau de bord étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    html, body {
  height: 100%;
  margin: 0;
}

body {
  display: flex;
  flex-direction: column;
  font-family: Arial, sans-serif;
  background-color: #f3efefd4;
}


    
    .navbar-custom {
      background-color: #e7962b;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .navbar-custom a.nav-link {
      color: #4a2b0c !important;
      font-weight: 500;
    }

    .navbar-custom .navbar-brand {
      color: white;
      font-weight: bold;
    }

    main {
      width: 70%;
      margin: 25px auto;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      flex: 1;
    }

    h2 {
      text-align: center;
      color: rgb(22, 92, 233);
      margin: 20px 0;
    }

    .notif {
      background-color: #e7962b;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      color: white;
      text-align: center;
    }

    .button a {
      border-radius: 4px;
      background-color: rgb(22, 92, 233);
      color: white;
      padding: 6px 12px;
      text-decoration: none;
    }

    footer {
      background-color: #424242;
    }

    footer p {
      font-style: italic;
      font-size: 0.9rem;
      margin-bottom: 0;
    }

    .info p {
      font-size: 1rem;
      margin-bottom: 6px;
    }
  </style>
</head>
  
  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
      <p class="navbar-brand">Titre de Séjour</p>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.html">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="#profil">Mon Profil</a></li>
        </ul>
      </div>
    </div>
  </nav>

  
  <main>
    <h2 id="welcome-message"></h2>
    <p class="notif" id="notification">Votre demande de titre de séjour est en cours de traitement.</p>

    <h4 class="text-center"><strong>Soumettez votre demande ici ⬇️</strong></h4>
    <div class="button d-flex justify-content-center mb-4">
      <a href="renouvellement.html">Demande</a>
    </div>

    <div class="requests-table">
      <h2>Mes Demandes</h2>
      <div class="table-responsive-md p-3">
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Date de la demande</th>
              <th>Statut</th>
              <th>Récépissé</th>
            </tr>
          </thead>
          <tbody id="requests-body"></tbody>
        </table>
      </div>
    </div>

    <section id="profil" class="mt-5">
      <h2>Mes Informations</h2>
      <div class="info">
        <p><strong>Nom:</strong> <span id="nom"></span></p>
        <p><strong>Prénom:</strong> <span id="prenom"></span></p>
        <p><strong>E-mail:</strong> <span id="email"></span></p>
        <p><strong>Statut:</strong> Étudiant</p>
      </div>
    </section>
  </main>

  
  <footer class="text-light text-center py-3 shadow">
    <div class="container">
      <hr class="border-light" />
      <p class="mb-1">Titre de séjour pour étudiants étrangers</p>
      <p class="mb-0">&copy; 2025 - Tous droits réservés</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
    // Récupérer les données PHP en JSON
    const etudiant = <?= $etudiant_json ?>;
    const demandes = <?= $demandes_json ?>;

    // Modifier le message de bienvenue
    document.getElementById("welcome-message").innerText = `Bienvenue sur votre tableau de bord, ${etudiant.prénom} ${etudiant.nom}`;

    // Afficher les informations de l'étudiant
    document.getElementById("nom").innerText = etudiant.nom;
    document.getElementById("prenom").innerText = etudiant.prénom;
    document.getElementById("email").innerText = etudiant.email;

    // Remplir le tableau avec les demandes de l'étudiant
    const tbody = document.getElementById("requests-body");
    tbody.innerHTML = ""; // Vider le tableau

     demandes.forEach((demande) => {
        const row = document.createElement("tr");

        // Si la demande est acceptée, afficher un lien vers le récépissé
        let statutCell = demande.statut;
        if (demande.statut === "Acceptée") {
            statutCell = `<a href="../php/recepisse.php?id_demande=${demande.id}" target="_blank" class="recepisse"> 📄 Voir récépissé</a>`;

        }

        row.innerHTML = `
            <td>${demande.date_de_dépôt}</td>
            <td>${demande.statut}</td>
            <td>${statutCell}</td>
        `;
        tbody.appendChild(row);
    });

    // Modifier la notification en fonction du statut de la dernière demande
    const notification = document.getElementById("notification");
    if (demandes.length > 0) {
        const derniereDemande = demandes[0]; // La plus récente
        switch (derniereDemande.statut) {
            case "Acceptée":
                notification.innerText = "✅ Félicitations ! Votre titre de séjour a été accepté.";
                notification.style.backgroundColor = "green";
                break;
            case "Rejetée":
                notification.innerText = "❌ Votre demande a été rejetée. Veuillez contacter l'administration.";
                notification.style.backgroundColor = "red";
                break;
            default:
                notification.innerText = "⏳ Votre demande de titre de séjour est en cours de traitement.";
                notification.style.backgroundColor = "orange";
        }
    } else {
        notification.innerText = "ℹ️ Vous n'avez pas encore fait de demande.";
        notification.style.backgroundColor = "gray";
    }
</script>

</body>
</html>
