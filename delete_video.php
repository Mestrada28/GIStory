<?php
require_once("config.php");
$conn = get_pdo_connection();
$data = json_decode(file_get_contents('php://input'), true);
$pointID = $data['pointID'];

$query = "DELETE FROM Videos WHERE pointID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$pointID]);

echo json_encode(['status' => 'success']);
?>