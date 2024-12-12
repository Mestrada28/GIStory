<?php
require_once("config.php");
$conn = get_pdo_connection();

$workspaceId = $_GET['id'];
$query = "SELECT workName FROM Workspaces WHERE WorkspaceID = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$workspaceId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode(['status' => 'success', 'title' => $result['workName']]);
} else {
    echo json_encode(['status' => 'error']);
}
?>