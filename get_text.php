<?php
require_once("config.php");
$conn = get_pdo_connection();
$data = json_decode(file_get_contents('php://input'), true);
$pointID = $data['pointID'];

$query = "SELECT Content FROM Texts WHERE pointID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$pointID]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
  $text = $result['Content'];
  echo json_encode(['status' => 'success', 'text' => $text]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Text not found']);
}
?>