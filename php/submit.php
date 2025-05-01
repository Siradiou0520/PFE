<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$database = "smi6";

$conn = new mysqli($servername, $username, $password, $database); // <-- CORRIGÉ ici (was $dbname)
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérification des champs
if (!isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['type'], $_POST['localite'])) {
    die("Tous les champs obligatoires ne sont pas remplis.");
}

$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$type = $_POST['type'];
$localite = trim($_POST['localite']);
$matricule = isset($_POST['matricule']) && !empty($_POST['matricule']) ? trim($_POST['matricule']) : NULL;

// Vérification email
if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    die("Erreur : Adresse email invalide !");
}

// Vérifier si email déjà existant dans la table correspondante
if ($type === "etudiant") {
    $check_sql = "SELECT id FROM etudiant WHERE email = ?";
} elseif ($type === "agent") {
    $check_sql = "SELECT id FROM agent WHERE email = ?";
} elseif ($type === "admin") {
    $check_sql = "SELECT id FROM admin WHERE email = ?";
}

$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    die("Erreur : Un utilisateur avec cet email existe déjà.");
}

// Cas étudiant
if ($type === "etudiant") {
    $sql = "INSERT INTO etudiant (nom, prénom, email, mot_de_passe, localité ) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nom, $prenom, $email, $password, $localite);

    if (!$stmt->execute()) {
        die("Erreur lors de l'inscription de l'étudiant: " . $stmt->error);
    }
    // Fermer la connexion
$stmt->close();
$conn->close();

// ✅ Rediriger vers la page de connexion après succès
header("Location: ../html/connecter.php");
exit();
// Cas agent
} elseif ($type === "agent") {
    if (empty($matricule)) {
        die("Le matricule est obligatoire pour un agent.");
    }

    $sql = "INSERT INTO agent (nom, prénom, email, mot_de_passe, matricule, localité) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nom, $prenom, $email, $password, $matricule, $localite);

    if (!$stmt->execute()) {
        die("Erreur lors de l'inscription de l'agent: " . $stmt->error);
    }
    // Fermer la connexion
$stmt->close();
$conn->close();

// ✅ Rediriger vers la page de connexion après succès
header("Location: ../html/connecter.php");
exit();
// ✅ Cas admin
} elseif ($type === "admin") {
    
    $sql = "INSERT INTO admin (nom, prénom, email, mot_de_passe, localité) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nom, $prenom, $email, $password, $localite);

    if (!$stmt->execute()) {
        die("Erreur lors de l'inscription de l'administrateur: " . $stmt->error);
    }
    // Fermer la connexion
$stmt->close();
$conn->close();

// ✅ Rediriger vers la page de connexion après succès
header("Location: ../html/connecter.php");
exit();
}

?>
