<?php
session_start();

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost"; // Adresse du serveur MySQL
$username = "root"; // Nom d'utilisateur MySQL
$password = ""; // Mot de passe MySQL
$database = "smi6"; // Nom de la base

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}

// Définir l'encodage des caractères
$conn->set_charset("utf8");

$email = $_POST['email'];
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
$type = $_POST['demande'];


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

// Vérifier si l'étudiant a déjà un numéro de dossier
$sql = "SELECT num_dossier FROM etudiant WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$dossier_numero = $row['num_dossier'];

if (empty($dossier_numero)) {
    // Générer un nouveau numéro de dossier
    $prefix = strtoupper(substr($local, 0, 4)); // Exemple: TETO pour Tétouan
    $date_soum = date("Ymd");
    $dossier_numero = $prefix . "-ETU" . $etudiant_id . "-" . $date_soum;

    // Mettre à jour la table etudiant
    $sql_update = "UPDATE etudiant SET num_dossier = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $dossier_numero, $etudiant_id);
    $stmt_update->execute();
}

// Insérer la demande
$sql_etudiant = "INSERT INTO demande (etudiant_id, numero_dossier, statut, agent_id, type_demande) VALUES (?, ?, 'en cours', ?, ?)";
$stmt = $conn->prepare($sql_etudiant);

if (!$stmt) {
    die("Erreur SQL lors de la préparation de la requête d'insertion : " . $conn->error);
}

$stmt->bind_param("isis", $etudiant_id, $dossier_numero, $agent_id, $type);

if (!$stmt->execute()) {
    die("Erreur lors de l'insertion de la demande : " . $stmt->error);
}



$uploadDir = "../uploads/"; // Dossier où enregistrer les fichiers
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 777, true);
}
// Liste des fichiers à uploader avec leurs noms
$documents_premiere = [ 
    "photo",
    "passeport",
    "contrat",
    "certificat",
    "casier",
    "scolarite",
    "bourse"
];

$documents_renouvellement = [ 
    "carte",
    "photo",
    "passeport",
    "contrat",
    "certificat",
    "casier",
    "scolarite",
    "bourse"
];

if ($type == "premiere_demande") {
    $documents = $documents_premiere;
} else {
    $documents = $documents_renouvellement;
}

$allowedExtensions = ["pdf", "jpg", "jpeg", "png"];
$maxSize = 5 * 1024 * 1024; // 5 Mo

foreach ($documents as $doc) {
    if (isset($_FILES[$doc]) && $_FILES[$doc]["error"] == 0) {
        $fileName = basename($_FILES[$doc]["name"]);
        $fileSize = $_FILES[$doc]["size"];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtensions)) {
            echo "Le fichier $doc n'est pas autorisé.<br>";
            continue;
        }

        if ($fileSize > $maxSize) {
            echo "Le fichier $doc est trop volumineux.<br>";
            continue;
        }

        $newFileName = uniqid($doc . "_") . "." . $fileExt;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES[$doc]["tmp_name"], $filePath)) {
            $sql = "INSERT INTO documents (etudiant_id, type_document, fichier_nom, fichier_chemin) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $etudiant_id, $doc, $newFileName, $filePath); // <- ici le fix
            if (!$stmt->execute()) {
                echo "Erreur lors de l'insertion du document $doc : " . $stmt->error . "<br>";
            } else {
                echo "Fichier $doc uploadé avec succès.<br>";
            }        
        } else {
            echo "Erreur lors de l'upload du fichier $doc.<br>"; 
        }
    }
}

// Succès
echo "Demande envoyée avec succès.";

$stmt->close();
$conn->close();

header('Location: ../html/dashbord2.php');
exit();

?>
