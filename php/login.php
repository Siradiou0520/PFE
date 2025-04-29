<?php
session_start();

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

$error = ""; // Initialiser la variable d'erreur

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vérification de l'email avec regex
if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    die("Erreur : Adresse email invalide !");
}

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier si l'utilisateur est un étudiant
        $queryEtudiant = "SELECT * FROM etudiant WHERE email = ?";
        $stmtEtudiant = $conn->prepare($queryEtudiant);
        $stmtEtudiant->bind_param('s', $email);
        $stmtEtudiant->execute();
        $resultEtudiant = $stmtEtudiant->get_result();

        if ($resultEtudiant->num_rows > 0) {
            $etudiant = $resultEtudiant->fetch_assoc();
            if (password_verify($password, $etudiant['mot_de_passe'])) {
                $_SESSION['user_id'] = $etudiant['id'];
                $_SESSION['role'] = 'etudiant';
                header('Location: http://localhost/PPFE/html/dashbord2.php');
                exit();
            } else {
                $error = "Mot de passe incorrect.";
            }
        }

        // Vérifier si l'utilisateur est un agent
        $queryAgent = "SELECT * FROM agent WHERE email = ?";
        $stmtAgent = $conn->prepare($queryAgent);
        $stmtAgent->bind_param('s', $email);
        $stmtAgent->execute();
        $resultAgent = $stmtAgent->get_result();

        if ($resultAgent->num_rows > 0) {
            $agent = $resultAgent->fetch_assoc();
            if (password_verify($password, $agent['mot_de_passe'])) {
                $_SESSION['user_id'] = $agent['id'];
                $_SESSION['role'] = 'agent';
                header('Location: ../html/agent.php');
                exit();
            } else {
                $error = "Mot de passe incorrect.";
            }
        }

        // Si l'utilisateur n'existe pas
        if (empty($error)) {
            $error = "Identifiants incorrects ou utilisateur inconnu.";
        }
    }
}

// Stocker l'erreur en session pour qu'elle soit affichée dans le formulaire
$_SESSION['error_message'] = $error;
header('Location: ../html/connecter.php');
exit();
?>
