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
$sql_demandes = "SELECT D.date_de_dépôt, D.statut FROM demande D INNER JOIN etudiant E ON E.id = D.etudiant_id WHERE D.etudiant_id = ? ORDER BY date_de_dépôt DESC";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3efefd4;
        }
        header {
            height: 70px;
            background-color: rgb(231, 150, 43);
            width: 100%;
        }
        .link {
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 22px;
        }
        a {
            text-decoration: none;
            color: white;
            font-size: 1.2em;
        }
        main {
            width: 70%;
            height: 100vh;
            margin: auto;
            margin-top: 25px;
            background-color: white;
        }
        h2 {
            text-align: center;
            color: rgb(22, 92, 233);
            margin: 20px 0;
        }
        .notif {
            background-color: rgb(231, 150, 43);
            padding: 10px;
            margin: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: white;
        }
        h4 {
            text-align: center;
            margin-bottom: 13px;
        }
        table {
            border-collapse: collapse;
            margin-bottom: 50px;
        }
        .button {
            display: flex;
            justify-content: center;
            gap: 7px;
        }
        .button a {
            border-radius: 3px;
            background-color:  rgb(22, 92, 233);
            box-shadow: none !important;
border: none;
outline: none;
color: white;
padding: 5px;
margin-bottom: 30px;
margin-top: 20px;
        }
        section {
            background-color: #f3efefd4;
            margin: 10px;
            padding: 5px;
            
        }
        section h2 {
            text-decoration: underline;
        }
        .requests-table h2 {
            text-decoration: underline;
        }
        footer{
    margin-top: 10%;
    font-style: italic;
    text-align: center;
    color: azure;
    background-color: darkgray;
    margin-bottom: -50px;
}
    </style>

</head>
<body>

<header>
    <div class="link">
        <a href="index.html">Accueil</a>
        <a href="#profil">Mon Profil</a>
    </div>
</header>

<main>
    <h2 id="welcome-message">Bienvenue sur votre tableau de bord</h2>
    <p class="notif" id="notification">Votre demande de titre de séjour est en cours de traitement.</p>

    <h4><strong>Soumettez votre demande ici ⬇️</strong></h4>
    <div class="button">
        <a href="renouvellement.html">Demande</a>
    </div>

    <div class="requests-table">
        <h2>Mes Demandes</h2>
        <table>
            <thead>
                <tr>
                    <th>Date de la demande</th>
                    <th>Statut</th>
            </thead>
            <tbody id="requests-body"></tbody>
        </table>
    </div>

    <section id="profil">
        <h2>Mes Informations</h2>
        <div class="info">
            <p><strong>Nom: </strong> <span id="nom"></span></p>
            <p><strong>Prénom: </strong> <span id="prenom"></span></p>
            <p><strong>E-mail: </strong> <span id="email"></span></p>
            <p><strong>Statut: </strong> Étudiant</p>
        </div>
    </section>
</main>

<footer>
    <hr/>
    <p>Titre de séjour pour étudiants étrangers</p>
    <p>&copy; Copyright 2025</p>
    <p>Tous droits réservés</p>
</footer>

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

    demandes.forEach(demande => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${demande.date_de_dépôt}</td>
            <td>${demande.statut}</td>
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
