<?php
require_once("config.php");

$workspaceID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$conn = get_pdo_connection();

$query = "
    SELECT 
        p.pointID,
        p.latitude, 
        p.longitude, 
        t.Content as text_content, 
        v.YouTubeURL as video_url, 
        pic.Path as image_path
    FROM 
        Points p
    LEFT JOIN 
        Texts t ON p.pointID = t.pointID
    LEFT JOIN 
        Videos v ON p.pointID = v.pointID
    LEFT JOIN 
        Pictures pic ON p.pointID = pic.pointID
    WHERE 
        p.WorkspaceID = :workspaceID
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
$stmt->execute();

$points = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process the fetched data
foreach ($points as &$point) {
    // Makes sure the image paths are correct
    if ($point['image_path']) {
        $point['image_path'] = 'images/' . basename($point['image_path']);
    }
    
    // Convert YouTube URL to embed URL if necessary
    if ($point['video_url']) {
        $point['video_url'] = convertToEmbedUrl($point['video_url']);
    }
}

echo json_encode(['status' => 'success', 'data' => $points]);

function convertToEmbedUrl($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    if (preg_match($pattern, $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return $url;
}
?>