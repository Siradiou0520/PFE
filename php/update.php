<?php 
$etudiant_id = $_POST['id'];

$sql_update = "UPDATE etudiant SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("si", $statut, $etudiant_id);
?>