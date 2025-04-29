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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demande_id = $_POST['demande_id'];
    $statut = $_POST['statut'];


    // Mettre à jour le statut dans la base de données
    $sql_update = "UPDATE demande SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("si", $statut, $demande_id);

    

    if ($stmt->execute()) {
        // Récupérer l'email de l'étudiant concerné
        $sql_etudiant = "SELECT e.email FROM etudiant e JOIN demande d ON d.etudiant_id = e.id WHERE d.id = ?";
        $stmt = $conn->prepare($sql_etudiant);
        $stmt->bind_param("i", $demande_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $etudiant = $result->fetch_assoc();

        // Récupérer l'email de l'agent de la même localité que l'étudiant
        $sql_agent = "SELECT a.email FROM etudiant e JOIN agent a ON e.localité = a.localité ";
        $stmt = $conn->prepare($sql_agent);
        $stmt->execute();
        $result = $stmt->get_result();
        $agent = $result->fetch_assoc();

        // Définition du message
        $message = ($statut == "Acceptée") ? 
            "✅ Félicitations ! Votre demande de titre de séjour a été accepté.\nVeuillez vous rendre dans la direction du service des étrangers de votre localité munis de votre numéro ou récipissé de demande, de vos documents légalisés et des frais de traitement de dossier après 20 jours." : 
            "❌ Votre demande a été rejetée par manque de justificatifs.\n Veuillez renvoyer une nouvelle demande en remplissant convenablement les champs avec les fichiers demandés.";

        // Envoyer un e-mail à l’étudiant
        $to = $etudiant['email'];
        $subject = "Mise à jour de votre demande de titre de séjour";
        $headers = "From:" . $agent['email'] ."\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body = "<p>Bonjour,<br><br>$message<br><br>Cordialement,<br>Service Administratif</p>";

        mail($to, $subject, $body, $headers);

        // Retourner un message de succès
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour"]);
    }
}
?>
