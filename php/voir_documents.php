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
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Vérifier si l'agent est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/connecter.php");
    exit();
}


$etudiant_id = $_GET['etudiant_id'];

// Vérifier si l'ID de l'étudiant est passé en paramètre
if (!isset($_GET['etudiant_id'])) {
    die("Aucun étudiant sélectionné.");
}

// Récupérer les fichiers de l'étudiant
$sql = "SELECT type_document, fichier_nom, fichier_chemin FROM documents WHERE etudiant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents de l'Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Documents de l'Étudiant</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type de Document</th>
                        <th>Nom du Fichier</th>
                        <th>Consultation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($doc = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['type_document']) ?></td>
                            <td><?= htmlspecialchars($doc['fichier_nom']) ?></td>
                            <td><a href="<?= htmlspecialchars($doc['fichier_chemin']) ?>" target="_blank" class="btn btn-primary">Consulter</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun document disponible pour cet étudiant.</p>
        <?php endif; ?>
        <a href="../html/agent.php" class="btn btn-secondary">Retour</a>
    </div>
</body>
</html>
