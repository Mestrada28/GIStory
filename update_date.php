<?php
session_start();
require_once("config.php");

// Collect data from the request
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['pointID'], $input['customDate'])) {
    $pointID = filter_var($input['pointID'], FILTER_SANITIZE_NUMBER_INT);
    $customDate = filter_var($input['customDate'], FILTER_SANITIZE_STRING);

    try {
        $conn = get_pdo_connection();
        $stmt = $conn->prepare("UPDATE Points SET CustomDate = :customDate WHERE pointID = :pointID");
        
        $stmt->bindParam(':pointID', $pointID);
        $stmt->bindParam(':customDate', $customDate);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update date']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or incomplete data provided']);
}
?>