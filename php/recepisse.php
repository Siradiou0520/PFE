<?php
session_start();
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('tfpdf/tfpdf.php'); // Assure-toi que tFPDF est bien installé dans ce dossier

$servername = "localhost"; // Adresse du serveur MySQL
$username = "root"; // Nom d'utilisateur MySQL
$password = ""; // Mot de passe MySQL
$database = "smi6"; // Nom de la base

// Connexion à la base
$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}

$etudiant_id = $_SESSION['user_id'];

// Récupérer l'ID de la demande
if (!isset($_GET['id_demande'])) {
    die("ID de la demande manquant.");
}

$id_demande = intval($_GET['id_demande']);

// Vérifier que cette demande appartient à l'étudiant et qu'elle est acceptée
$sql = "SELECT D.date_de_dépôt, E.nom, E.prénom, E.email, D.numero_dossier 
        FROM demande D 
        INNER JOIN etudiant E ON D.etudiant_id = E.id 
        WHERE D.id = ? AND D.etudiant_id = ? AND D.statut = 'Acceptée'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_demande, $etudiant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Aucune demande acceptée trouvée ou vous n'avez pas les droits.");
}

$data = $result->fetch_assoc();

// Générer le PDF avec tFPDF
$pdf = new tFPDF();
$pdf->AddPage();

// Ajouter la police DejaVuSans (police compatible UTF-8)
$pdf->AddFont('DejaVu','','DejaVuSans.ttf', true);
$pdf->SetFont('DejaVu','',16);

// Titre
$pdf->Cell(0, 10, 'Récépissé de demande de titre de séjour', 0, 1, 'C');
$pdf->Ln(10);

// Contenu du PDF
$pdf->SetFont('DejaVu','',14);
$pdf->Cell(0, 10, 'Dossier N° : ' . $data['numero_dossier'], 0, 1);



$pdf->SetFont('DejaVu','',12);
$pdf->Cell(0, 10, 'Nom : ' . $data['nom'], 0, 1);
$pdf->Cell(0, 10, 'Prénom : ' . $data['prénom'], 0, 1);
$pdf->Cell(0, 10, 'Email : ' . $data['email'], 0, 1);
$pdf->Cell(0, 10, 'Date de dépôt : ' . $data['date_de_dépôt'], 0, 1);
$pdf->Cell(0, 10, 'Statut : Acceptée', 0, 1);

$pdf->Ln(10);
$pdf->MultiCell(0, 10, "Ce document fait office de récépissé officiel de votre demande de titre de séjour. Veuillez le conserver précieusement.");

// Sortie du PDF
$pdf->Output('D', 'recepisse.pdf');
?>
