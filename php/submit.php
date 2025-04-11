<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smi6";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier que les données sont bien reçues
if (!isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['type'], $_POST['localite'])) {
    die("Tous les champs obligatoires ne sont pas remplis.");
}


// Récupération des données
$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$type = $_POST['type'];
$localite = trim($_POST['localite']);
$matricule = isset($_POST['matricule']) && !empty($_POST['matricule']) ? trim($_POST['matricule']) : NULL;

// Vérification de l'email avec regex
if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    die("Erreur : Adresse email invalide !");
}

// Vérifier si l'utilisateur existe déjà
$check_sql = "SELECT id FROM etudiant WHERE email = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
/*if ($result->num_rows > 0) {
    die("Cet e-mail est déjà utilisé.");
}*/

// Inscription en fonction du type
if ($type === "etudiant") {
    // Insérer l'étudiant dans la table `utilisateur`
    $sql = "INSERT INTO etudiant (nom, prénom, email, mot_de_passe, localité ) VALUES (?, ?, ?, ?, ? )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nom, $prenom, $email, $password, $localite);
    
    if (!$stmt->execute()) {
        die("Erreur lors de l'inscription de l'étudiant: " . $stmt->error);
    }

    //$etudiant_id = $stmt->insert_id;

    // Trouver un agent de la même localité
    /*$sql = "SELECT id FROM agent WHERE localité = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $localite);
    $stmt->execute();
    $result = $stmt->get_result();
    $agent = $result->fetch_assoc();
    $agent_id = $agent ? $agent['id'] : NULL;

    // Insérer une demande de titre de séjour
    $sql = "INSERT INTO demande (etudiant_id, statut, agent_id) VALUES (?, 'encours', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $etudiant_id, $agent_id);
    if (!$stmt->execute()) {
        die("Erreur lors de la création de la demande: " . $stmt->error);
    }*/
     
    //Fermer la connexion
$stmt->close();
$conn->close();

//✅ Rediriger vers la page de connexion après succès
header("Location: ../html/connecter.php");
exit();

}   elseif ($type === "agent") {
    // Insérer un agent dans la table `agent`
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
}
?>


