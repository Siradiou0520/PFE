<?php
session_start();

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "smi6";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}

// Définir l'encodage des caractères
$conn->set_charset("utf8");


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    //die("Utilisateur non connecté.");
    header("Location: ../html/connecter.php");
}
// Vérification de l'email avec regex
if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    die("Erreur : Adresse email invalide !");
}

$etudiant_id = $_SESSION['user_id'];
$local = $_POST['adresse'] ?? '';

// Vérifier que l'adresse n'est pas vide
if (empty($local)) {
    die("Erreur : Localité non spécifiée.");
}

// Rechercher l'agent correspondant à la localité
$sql = "SELECT id FROM agent WHERE localité = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête : " . $conn->error);
}

$stmt->bind_param("s", $local);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();

// Vérifier si un agent a été trouvé
if (!$agent) {
    die("Aucun agent trouvé pour cette localité.");
}

$agent_id = $agent['id'];

// Insérer la demande
$sql_etudiant = "INSERT INTO demande (etudiant_id, statut, agent_id) VALUES (?, 'en cours', ?)";
$stmt = $conn->prepare($sql_etudiant);

if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête d'insertion : " . $conn->error);
}

$stmt->bind_param("ii", $etudiant_id, $agent_id);

if (!$stmt->execute()) {
    die("Erreur lors de l'insertion de la demande : " . $stmt->error);
}

// Succès
echo "Demande envoyée avec succès.";

$stmt->close();
$conn->close();
?>
