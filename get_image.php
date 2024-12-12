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
    echo json_encode(['status' => 'success', 'imagePath' => $imagePath]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Image not found']);
}
?>