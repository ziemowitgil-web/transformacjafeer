<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');

// Autoryzacja modułu
$headers = getallheaders();
if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer ' . API_AUTH_TOKEN) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Pobranie danych JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['action']) || !isset($data['userId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'userId i action są wymagane']);
    exit;
}

$action = $data['action'];
$userId = $data['userId'];

// Funkcja wykonująca akcję w Okta
function performOktaAction($action, $userId) {
    $url = OKTA_DOMAIN . "/api/v1/users/{$userId}/lifecycle";
    $method = "";

    if ($action === 'unlock') {
        $url .= "/unlock";
        $method = "POST";
    } elseif ($action === 'deactivate') {
        $url .= "/deactivate";
        $method = "POST";
    } else {
        return ['status' => 400, 'response' => json_encode(['error' => 'Nieznana akcja'])];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: SSWS " . OKTA_API_TOKEN,
        "Accept: application/json",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'response' => $response];
}

// Wykonanie akcji
$result = performOktaAction($action, $userId);
http_response_code($result['status']);
echo $result['response'];
