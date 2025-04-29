<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'config.php'; // fichier de connexion MySQL

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

// Requête : étudiants dont le titre expire dans 7 jours
$query = "SELECT nom, email, date_expiration FROM utilisateur 
          WHERE DATE(date_expiration) = CURDATE() + INTERVAL 7 DAY";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ton.email@gmail.com';
        $mail->Password = 'ton_mot_de_passe'; // ou app password si 2FA
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ton.email@gmail.com', 'Titre de séjour');
        $mail->addAddress($row['email'], $row['nom']);

        $mail->Subject = '🔔 Rappel : Renouvellement de ton titre de séjour';
        $mail->Body    = "Bonjour " . $row['nom'] . ",\n\nTon titre de séjour expire dans 7 jours (le " . $row['date_expiration'] . "). Pense à le renouveler !\n\nBonne journée.";

        $mail->send();
        echo "Rappel envoyé à " . $row['email'] . "\n";
    } catch (Exception $e) {
        echo "Erreur pour " . $row['email'] . " : {$mail->ErrorInfo}\n";
    }
}

$conn->close();
