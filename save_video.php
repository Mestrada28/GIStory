<?php
require_once("config.php");
$conn = get_pdo_connection();
$data = json_decode(file_get_contents('php://input'), true);
$pointID = $data['pointID'];
$videoUrl = $data['videoUrl'];

// Delete the existing video for the point
$deleteQuery = "DELETE FROM Videos WHERE pointID = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->execute([$pointID]);

// Insert the new video for the point
$insertQuery = "INSERT INTO Videos (pointID, YouTubeURL) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->execute([$pointID, $videoUrl]);

echo json_encode(['status' => 'success']);
?>