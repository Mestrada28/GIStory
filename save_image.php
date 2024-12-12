<?php
require_once("config.php");

$conn = get_pdo_connection();

$pointID = $_POST['pointID'];
$image = $_FILES['image'];

if ($image['error'] === UPLOAD_ERR_OK) {
    $imagePath = 'images/' . uniqid() . '_' . $image['name'];
    move_uploaded_file($image['tmp_name'], $imagePath);

    $query = "INSERT INTO Pictures (WorkspaceID, pointID, Path) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['WorkspaceID'], $pointID, $imagePath]);

    echo "Image saved successfully";
} else {
    echo "Error uploading image";
}
?>