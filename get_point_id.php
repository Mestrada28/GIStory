<?php
require_once("config.php");
$conn = get_pdo_connection();
$data = json_decode(file_get_contents('php://input'), true);
$latitude = $data['latitude'];
$longitude = $data['longitude'];

$query = "SELECT pointID FROM Points WHERE latitude = ? AND longitude = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$latitude, $longitude]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
  $pointID = $result['pointID'];
  echo $pointID;
} else {
  echo "Point not found";
}
?>