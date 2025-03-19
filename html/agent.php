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

// Vérifier si l'agent est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}

$agent_id = $_SESSION['user_id'];

// Récupérer les infos de l'agent connecté
$sql_agent = "SELECT * FROM agent WHERE id = ?";
$stmt = $conn->prepare($sql_agent);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();

// Récupérer les demandes des étudiants de la même localité que l'agent
$sql_demandes = "SELECT e.nom, e.prénom, e.email, e.id, d.id AS demande_id, d.date_de_dépôt, d.statut 
                 FROM demande d
                 JOIN etudiant e ON d.etudiant_id = e.id
                 WHERE d.agent_id = ?";
$stmt = $conn->prepare($sql_demandes);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$demandes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3efefd4;
        }
        header {
            height: 70px;
            background-color: green;
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
            width: 80%;
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
        .profil {
            background-color: white;
            margin: 10px;
            padding: 5px;
            height: 80%;
            margin-top: 25px;
        }
        .profil h2 {
            text-decoration: underline;
        }
        .requests-table h2 {
            text-decoration: underline;
           font-weight: bold;
           padding-bottom: 20px;
        }
        footer{
    margin-top: 10%;
    font-style: italic;
    text-align: center;
    color: azure;
    background-color: darkgray;
    margin-bottom: -50px;
        }
        .contain{
            display: flex;
            justify-content: space-between;
        }

        .td_a {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgb(22, 92, 233);;
       width: 50px;
       border-radius: 3px;
        }

        .td_a a:hover{
            text-decoration: underline;
            color: black;
        }
    </style>
</head>
<body>
    <header>
        <div class="link">
            <a href="index.html">Acceuil</a>
            <a href="#profil">Mon Profil</a>
        </div>
    </header>
    <div class="contain">
    <main>
        <h2>Bienvenue Cher(e) Agent </h2>
        <div class="requests-table">
            <h2>Liste de vos demandes</h2>
            <div class="table-responsive-md p-5">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Date de dépôt</th>
                        <th>Statut</th>
                        <th>Documents</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    <?php while ($demande = $demandes->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($demande['nom']) ?></td>
            <td><?= htmlspecialchars($demande['prénom']) ?></td>
            <td><?= htmlspecialchars($demande['email']) ?></td>
            <td><?= htmlspecialchars($demande['date_de_dépôt']) ?></td>
            <td id="statut-<?= $demande['demande_id'] ?>"><?= htmlspecialchars($demande['statut']) ?></td>
            <td> <div class="td_a"> <a href="#">Voir</a> </div> </td>
            <td>
                <button onclick="updateStatus(<?= $demande['demande_id'] ?>, 'Acceptée')">Accepter</button>
                <button onclick="updateStatus(<?= $demande['demande_id'] ?>, 'Rejetée')">Rejeter</button>
            </td>
        </tr>
    <?php } ?>
</tbody>
            </table>
        </div>
        </div>
    </main>
    <div class="profil">
    <h2>Mes Informations</h2>
    <div class="info">
        <p><strong>Nom: </strong><?= htmlspecialchars($agent['nom']) ?></p>
        <p><strong>Prénom: </strong><?= htmlspecialchars($agent['prénom']) ?></p>
        <p><strong>E-mail: </strong><?= htmlspecialchars($agent['email']) ?></p>
        <p><strong>Statut: </strong>Agent Administratif</p>
    </div>
</div>
</div>
    <footer>
        <hr/>
        <p>Titre de séjour pour étudiants étrangers </p>
        <p>&copy;Copyright 2025</p>
        <p>Tous droits réservés</p>
    </footer>
</body>
<script>
function updateStatus(demandeId, statut) {
    if (confirm("Voulez-vous vraiment " + (statut === 'Acceptée' ? "accepter" : "rejeter") + " cette demande ?")) {
        fetch('../php/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'demande_id=' + encodeURIComponent(demandeId) + '&statut=' + encodeURIComponent(statut)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("statut-" + demandeId).innerText = statut;
                alert("Statut mis à jour avec succès !");
            } else {
                alert("Erreur: " + data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
}

</script>
</html>