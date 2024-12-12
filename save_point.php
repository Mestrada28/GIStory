<?php
session_start();
require_once("config.php");

// Collect data from the request
$input = json_decode(file_get_contents('php://input'), true);

// Prepare and execute the database query
if (isset($input['latitude'], $input['longitude'], $input['workspaceID'])) {
    $latitude = filter_var($input['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_var($input['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $workspaceID = filter_var($input['workspaceID'], FILTER_SANITIZE_NUMBER_INT);

    // Handle the custom date. If not provided remains NULL
    $customDate = !empty($input['customDate']) ? $input['customDate'] : null;

    $conn = get_pdo_connection();
    $stmt = $conn->prepare("INSERT INTO Points (WorkspaceID, latitude, longitude, CustomDate) 
                           VALUES (:workspaceID, :latitude, :longitude, :customDate)");

    $stmt->bindParam(':workspaceID', $workspaceID);
    $stmt->bindParam(':latitude', $latitude);
    $stmt->bindParam(':longitude', $longitude);
    $stmt->bindParam(':customDate', $customDate);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save point']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or incomplete data provided']);
}
?>
