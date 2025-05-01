<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}

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

// Suppression de la demande et de l'étudiant lié
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['supprimer_demande'])) {
    $etudiant_id = intval($_POST['supprimer_demande']);

    // D'abord supprimer la demande
    $sql = "DELETE FROM demande WHERE id = ? ";
    $stmt = $conn->prepare($sql);
    // Vérifier si la requête a été bien préparée
if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête étudiant : " . $conn->error);
}
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Suppression d’un agent si l’ID est passé en POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['supprimer_agent'])) {
    $id = intval($_POST['supprimer_agent']);
    $sql = "DELETE FROM agent WHERE id = ? ";
    $stmt = $conn->prepare($sql);
    // Vérifier si la requête a été bien préparée
if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête étudiant : " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout d’un agent
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajouter_agent'])) {
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $email = $conn->real_escape_string($_POST['email']);
    $mdp = $conn->real_escape_string($_POST['mdp']);
    $matricule = $conn->real_escape_string($_POST['matricule']);
    $localite = $conn->real_escape_string($_POST['localite']);

    $sql = "INSERT INTO agent (nom, prénom, email, mot_de_passe, matricule, localité) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // Vérifier si la requête a été bien préparée
if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête étudiant : " . $conn->error);
}
$stmt->bind_param("ssssss", $nom, $prenom, $email, $mdp, $matricule, $localite);
$stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Récupération des demandes
$sqlDemandes = "SELECT d.id, e.nom, e.prénom, d.statut, d.type_demande, d.date_de_dépôt
                FROM demande d
                JOIN etudiant e ON d.etudiant_id = e.id";
$demandes = $conn->query($sqlDemandes);

// Récupération des agents
$sqlAgents = "SELECT * FROM agent";
$agents = $conn->query($sqlAgents);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Titre de séjour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    html, body {
        height: 100%;
        margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    .container {
        flex: 1; /* prend tout l'espace dispo entre le header et le footer */
    }

    footer {
        background-color: #6c757d; /* ton bg-secondary Bootstrap */
        color: white;
    }
</style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
        <a class="navbar-brand" href="#">Admin | Titre de séjour</a>
        <div class="ms-auto">
            <a href="../php/logout.php" class="btn btn-outline-light">Déconnexion</a>
        </div>
    </nav>

    <form class="row g-3 align-items-center my-3" id="filtre-form">
    <div class="col-auto">
        <label for="filtre-statut" class="form-label">Filtrer par statut :</label>
    </div>
    <div class="col-auto">
        <select id="filtre-statut" class="form-select">
            <option value="">Tous</option>
            <option value="en cours">en cours</option>
            <option value="acceptée">Acceptée</option>
            <option value="rejetée">Rejetée</option>
        </select>
    </div>
</form>


    <div class="container my-4">
        <h2>📁 Liste des demandes</h2>
        <div id="liste-demandes"></div>
         
        <hr>

        <h2>👤 Liste des agents</h2>
        <table class="table table-hover mt-3">
            <thead class="table-secondary">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Localité</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
<?php while ($row = $agents->fetch_assoc()) : ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['nom'] . ' ' . $row['prénom']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['localité']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm('Supprimer cet agent ?');">
                <input type="hidden" name="supprimer_agent" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
        </td>
    </tr>
<?php endwhile; ?>
</tbody>
        </table>
    </div>
    <h3 class="mt-5">➕ Ajouter un agent</h3>
<form method="post" class="row g-3 mt-2">
    <input type="hidden" name="ajouter_agent" value="1">
    <div class="col-md-3">
        <input type="text" name="nom" class="form-control" placeholder="Nom" required>
    </div>
    <div class="col-md-3">
        <input type="text" name="prenom" class="form-control" placeholder="Prénom" required>
    </div>
    <div class="col-md-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
    </div>
    <div class="col-md-3">
        <input type="text" name="mdp" class="form-control" placeholder="Mot de passe" required>
    </div>
    <div class="col-md-3">
        <input type="text" name="matricule" class="form-control" placeholder="Matricule" required>
    </div>
    <div class="col-md-3">
        <input type="text" name="localite" class="form-control" placeholder="Localité" required>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Ajouter l’agent</button>
    </div>
</form>


    <footer class="text-center text-muted py-3 bg-secondary border-top mt-4">
    <p>Titre de séjour pour étudiants étrangers</p>
        <p>&copy; Copyright 2025-Tous droits réservés</p>
        
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
function filtrerDemandes() {
    const statut = document.getElementById('filtre-statut').value;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "../php/filtrer_demandes.php?statut=" + encodeURIComponent(statut), true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("liste-demandes").innerHTML = xhr.responseText;
        } else {
            document.getElementById("liste-demandes").innerHTML = "Erreur lors du chargement.";
        }
    };

    xhr.send();
}

// Chargement initial des demandes
window.onload = function () {
    filtrerDemandes(); // affiche toutes les demandes au chargement
    document.getElementById('filtre-statut').addEventListener('change', filtrerDemandes); // active le filtre dynamique
};

</script>

</body>
</html>
