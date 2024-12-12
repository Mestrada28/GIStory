<?php
require_once("config.php");

// Get the point ID from the request
$pointID = json_decode(file_get_contents('php://input'), true)['pointID'];

// Delete the point from the Points table
$conn = get_pdo_connection();
$stmt = $conn->prepare("DELETE FROM Points WHERE pointID = :pointID");
$stmt->bindParam(':pointID', $pointID, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(['status' => 'success']);
?>
