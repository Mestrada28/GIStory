<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = $data['message'];
    // Gemini API key for the AI 
    $API_KEY = 'YOUR_GEMINI_API_KEY'; 
    $API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    $ch = curl_init($API_URL . '?key=' . $API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'contents' => [[
            'parts' => [[
                'text' => $message
            ]]
        ]]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['response' => $aiResponse]);
    } else {
        echo json_encode(['error' => 'API request failed']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>