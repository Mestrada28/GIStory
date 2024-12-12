<?php
require_once("config.php");

$conn = get_pdo_connection();

$data = json_decode(file_get_contents('php://input'), true);
$pointID = $data['pointID'];

$query = "SELECT Path FROM Pictures WHERE pointID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$pointID]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
  $imagePath = $result['Path'];
  if (file_exists($imagePath)) {
    unlink($imagePath);
  }

  $query = "DELETE FROM Pictures WHERE pointID = ?";
  $stmt = $conn->prepare($query);
  $stmt->execute([$pointID]);

  echo "Image deleted successfully";
} else {
  echo "Image not found";
}
?>