<?php
require_once("config.php");

try {
    // Get the user ID from the session
    session_start();
    $userID = $_SESSION['userID'];
    
    $conn = get_pdo_connection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete all pictures associated with user's workspaces or points
    $stmt = $conn->prepare("
        DELETE FROM Pictures 
        WHERE WorkspaceID IN (SELECT WorkspaceID FROM Workspaces WHERE userID = :userID)
        OR pointID IN (SELECT p.pointID FROM Points p 
                      JOIN Workspaces w ON p.WorkspaceID = w.WorkspaceID 
                      WHERE w.userID = :userID)
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all videos associated with user's workspaces or points
    $stmt = $conn->prepare("
        DELETE FROM Videos 
        WHERE WorkspaceID IN (SELECT WorkspaceID FROM Workspaces WHERE userID = :userID)
        OR pointID IN (SELECT p.pointID FROM Points p 
                      JOIN Workspaces w ON p.WorkspaceID = w.WorkspaceID 
                      WHERE w.userID = :userID)
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all texts associated with user's workspaces or points
    $stmt = $conn->prepare("
        DELETE FROM Texts 
        WHERE WorkspaceID IN (SELECT WorkspaceID FROM Workspaces WHERE userID = :userID)
        OR pointID IN (SELECT p.pointID FROM Points p 
                      JOIN Workspaces w ON p.WorkspaceID = w.WorkspaceID 
                      WHERE w.userID = :userID)
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete all lines associated with user's workspaces
    $stmt = $conn->prepare("
        DELETE FROM Line 
        WHERE WorkspaceID IN (SELECT WorkspaceID FROM Workspaces WHERE userID = :userID)
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all points associated with user's workspaces
    $stmt = $conn->prepare("
        DELETE FROM Points 
        WHERE WorkspaceID IN (SELECT WorkspaceID FROM Workspaces WHERE userID = :userID)
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all workspaces associated with user
    $stmt = $conn->prepare("DELETE FROM Workspaces WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    // At the end delete the user
    $stmt = $conn->prepare("DELETE FROM Users WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Destroy session
    session_destroy();
    
    // Send success response before redirect
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Account successfully deleted']);
    exit;
    
} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error
    error_log("Account deletion error: " . $e->getMessage());
    
    // Send error response
    header('Content-Type: application/json');
    // Set appropriate error status code
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
    exit;
}
?>
