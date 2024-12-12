<?php
require_once("config.php");

try {
    // Get the workspace ID from the request
    $workspaceID = json_decode(file_get_contents('php://input'), true)['workspaceID'];
    
    $conn = get_pdo_connection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // First, delete all references in Pictures table
    $stmt = $conn->prepare("DELETE FROM Pictures WHERE WorkspaceID = :workspaceID");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete point-specific pictures
    $stmt = $conn->prepare("DELETE FROM Pictures WHERE pointID IN (SELECT pointID FROM Points WHERE WorkspaceID = :workspaceID)");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all references in Videos table
    $stmt = $conn->prepare("DELETE FROM Videos WHERE WorkspaceID = :workspaceID");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete point-specific videos
    $stmt = $conn->prepare("DELETE FROM Videos WHERE pointID IN (SELECT pointID FROM Points WHERE WorkspaceID = :workspaceID)");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all references in Texts table
    $stmt = $conn->prepare("DELETE FROM Texts WHERE WorkspaceID = :workspaceID");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete point-specific texts
    $stmt = $conn->prepare("DELETE FROM Texts WHERE pointID IN (SELECT pointID FROM Points WHERE WorkspaceID = :workspaceID)");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete all records from Line table associated with this workspace
    $stmt = $conn->prepare("DELETE FROM Line WHERE WorkspaceID = :workspaceID");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete all points
    $stmt = $conn->prepare("DELETE FROM Points WHERE WorkspaceID = :workspaceID");
    $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete the workspace itself separately to handle any potential issues
    try {
        $stmt = $conn->prepare("DELETE FROM Workspaces WHERE WorkspaceID = :workspaceID");
        $stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            // Get the error info
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Failed to delete workspace: " . $errorInfo[2]);
        }
        
        // Check if any rows were actually deleted
        if ($stmt->rowCount() === 0) {
            throw new Exception("No workspace found with ID: " . $workspaceID);
        }
    } catch (Exception $e) {
        // Roll back the transaction and throw the error
        $conn->rollBack();
        throw new Exception("Error deleting workspace: " . $e->getMessage());
    }
    
    // Commit the transaction if everthing looks good
    $conn->commit();
    
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    // Roll back transaction on error
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Workspace deletion error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
