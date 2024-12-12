<?php
require_once("config.php");

// Get the point ID from the request
$pointID = json_decode(file_get_contents('php://input'), true)['pointID'];

// Delete associated media from Pictures, Texts, and Videos tables
$conn = get_pdo_connection();

// Delete image files and records from Pictures table
$stmt = $conn->prepare("SELECT Path FROM Pictures WHERE pointID = :pointID");
$stmt->bindParam(':pointID', $pointID, PDO::PARAM_INT);
$stmt->execute();
$imagePaths = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($imagePaths as $path) {
    if (file_exists($path)) {
        unlink($path);
    }
}

$stmt = $conn->prepare("DELETE FROM Pictures WHERE pointID = :pointID");
$stmt->bindParam(':pointID', $pointID, PDO::PARAM_INT);
$stmt->execute();

// Delete from Texts table
$stmt = $conn->prepare("DELETE FROM Texts WHERE pointID = :pointID");
$stmt->bindParam(':pointID', $pointID, PDO::PARAM_INT);
$stmt->execute();

// Delete from Videos table
$stmt = $conn->prepare("DELETE FROM Videos WHERE pointID = :pointID");
$stmt->bindParam(':pointID', $pointID, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(['status' => 'success']);
?>