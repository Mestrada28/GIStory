<?php
require_once("config.php");
$conn = get_pdo_connection();
$data = json_decode(file_get_contents('php://input'), true);
$pointID = $data['pointID'];

$query = "SELECT YouTubeURL FROM Videos WHERE pointID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$pointID]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
  $videoUrl = $result['YouTubeURL'];
  echo json_encode(['status' => 'success', 'videoUrl' => $videoUrl]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Video not found']);
}
?>