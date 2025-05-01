<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$database = "smi6";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$statut = isset($_GET['statut']) ? $_GET['statut'] : '';

$sql = "SELECT d.id, d.etudiant_id, e.nom, e.prénom, d.statut, d.date_de_dépôt 
        FROM demande d 
        JOIN etudiant e ON d.etudiant_id = e.id";

if (!empty($statut)) {
    $sql .= " WHERE d.statut = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $statut);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table class="table table-bordered">';
    echo '<thead><tr><th>ID DEMANDE</th><th>Nom & Prénom</th><th>Statut</th><th>Date</th><th>Action</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row["id"] . '</td>';
        echo '<td>' . htmlspecialchars($row["nom"] . ' ' . $row["prénom"]) . '</td>';
        echo '<td>' . htmlspecialchars($row["statut"]) . '</td>';
        echo '<td>' . htmlspecialchars($row["date_de_dépôt"]) . '</td>';
        echo '<td>
            <form method="post" onsubmit="return confirm(\'Supprimer cette demande et l’étudiant associé ?\');">
                <input type="hidden" name="supprimer_demande" value="' . $row["id"] . '">
                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
          </td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo "Aucune demande trouvée.";
}

$stmt->close();
$conn->close();
?>
