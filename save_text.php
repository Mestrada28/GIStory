<?php
require_once("config.php");
$conn = get_pdo_connection();
$data = json_decode(file_get_contents('php://input'), true);
$pointID = $data['pointID'];
$text = $data['text'];

// Delete the existing text for the point
$deleteQuery = "DELETE FROM Texts WHERE pointID = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->execute([$pointID]);

// Insert the new text for the point
$insertQuery = "INSERT INTO Texts (pointID, Content) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->execute([$pointID, $text]);

echo json_encode(['status' => 'success']);
?>