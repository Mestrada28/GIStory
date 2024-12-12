<?php
require_once("config.php");

$workspaceID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$conn = get_pdo_connection();

$query = "
SELECT 
    p.pointID,
    p.latitude,
    p.longitude,
    p.CustomDate,
    p.DateCreated,
    (SELECT Content FROM Texts WHERE pointID = p.pointID LIMIT 1) as text_content,
    (SELECT YouTubeURL FROM Videos WHERE pointID = p.pointID LIMIT 1) as video_url,
    (SELECT Path FROM Pictures WHERE pointID = p.pointID LIMIT 1) as image_path
FROM Points p
WHERE p.WorkspaceID = :workspaceID
GROUP BY 
    p.pointID,
    p.latitude,
    p.longitude,
    p.CustomDate,
    p.DateCreated
ORDER BY 
    COALESCE(p.CustomDate, p.DateCreated) ASC;
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
$stmt->execute();

$points = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $points]);
?>