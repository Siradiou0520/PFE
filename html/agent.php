<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Connexion à la base de données
$servername = "localhost"; // Adresse du serveur MySQL
$username = "root"; // Nom d'utilisateur MySQL
$password = ""; // Mot de passe MySQL
$database = "smi6"; // Nom de la base

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
$conn->set_charset("utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}

$agent_id = $_SESSION['user_id'];

$sql_agent = "SELECT * FROM agent WHERE id = ?";
$stmt = $conn->prepare($sql_agent);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3efefd4;
        }
        .header-links a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
        }
        .header-links a:hover {
            text-decoration: underline;
        }
        footer {
            background-color: darkgray;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
            text-align: center;
            font-style: italic;
        }
        .td_a {
            background-color: rgb(22, 92, 233);
            border-radius: 5px;
            padding: 5px;
            text-align: center;
        }
        .td_a a {
            color: white;
            text-decoration: none;
        }
        .td_a a:hover {
            text-decoration: underline;
        }
        button {
            background-color: rgb(22, 92, 233);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        button:hover {
            background-color: rgb(18, 78, 200);
        }
    </style>
</head>
<body>

<header class="bg-success">
    <div class="container py-3 d-flex justify-content-center header-links gap-4 flex-wrap">
        <a href="index.html">Accueil</a>
        <a href="#profil">Mon Profil</a>
    </div>
</header>

<div class="container my-4">
    <h2 class="text-center text-primary">Bienvenue Cher(e) Agent</h2>

    <div class="row g-4 mt-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Liste de vos demandes</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-primary">
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
                                    <td>
                                        <div class="td_a">
                                            <a href="../php/voir_documents.php?etudiant_id=<?= $demande['id'] ?>">Voir</a>
                                        </div>
                                        <td class="d-flex flex-column gap-2" id="action-<?= $demande['demande_id'] ?>">
                                        <?php if ($demande['statut'] === 'en cours') { ?>
                                        <button onclick="updateStatus(<?= $demande['demande_id'] ?>, 'Acceptée')">Accepter</button>
                                        <button onclick="updateStatus(<?= $demande['demande_id'] ?>, 'Rejetée')">Rejeter</button>
                                        <?php } else { ?>
                                        <span class="text-success fw-bold">Action réalisée</span>
                                        <?php } ?>
                                        </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil -->
        <div class="col-lg-4" id="profil">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Mes Informations</h3>
                    <p><strong>Nom:</strong> <?= htmlspecialchars($agent['nom']) ?></p>
                    <p><strong>Prénom:</strong> <?= htmlspecialchars($agent['prénom']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($agent['email']) ?></p>
                    <p><strong>Statut:</strong> Agent Administratif</p>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-secondary text-light text-center py-3 shadow">
    <div class="container">
        <hr>
        <p>Titre de séjour pour étudiants étrangers</p>
        <p>&copy; Copyright 2025</p>
        <p>Tous droits réservés</p>
    </div>
</footer>

<script>
function updateStatus(demandeId, statut) {
    if (confirm("Voulez-vous vraiment " + (statut === 'Acceptée' ? "accepter" : "rejeter") + " cette demande ?")) {

        // Désactivation immédiate des boutons pour éviter double clic
        const actionCell = document.getElementById("action-" + demandeId);
        const buttons = actionCell.querySelectorAll("button");
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = 0.5;
            btn.style.cursor = "not-allowed";
        });

        fetch('../php/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'demande_id=' + encodeURIComponent(demandeId) + '&statut=' + encodeURIComponent(statut)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mise à jour du statut
                document.getElementById("statut-" + demandeId).innerText = statut;

                // Remplacer les boutons par un message
                actionCell.innerHTML = '<span class="text-success fw-bold">Action réalisée</span>';

                alert("Statut mis à jour avec succès !");
            } else {
                alert("Erreur: " + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            });
    }
}
</script>

</body>
</html>
