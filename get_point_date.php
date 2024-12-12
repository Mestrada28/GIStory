<?php
session_start();
require_once("config.php");

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['pointID'])) {
    $pointID = filter_var($input['pointID'], FILTER_SANITIZE_NUMBER_INT);

    try {
        $conn = get_pdo_connection();
        $stmt = $conn->prepare("SELECT CustomDate FROM Points WHERE pointID = :pointID");
        $stmt->bindParam(':pointID', $pointID);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'customDate' => $result['CustomDate']
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No date found for this point'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Point ID not provided'
    ]);
}
?>