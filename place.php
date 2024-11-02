<?php
$pdo = new PDO('mysql:host=localhost;dbname=flightpool;charset=utf8', 'root', null, [ 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
]);

// Токен, который приходит
$token = getallheaders()['Authorization'];

// Запрос к базе данных для получения document_number по токену
$stmt = $pdo->prepare("SELECT document_number FROM users WHERE api_token = :token");
$stmt->execute(['token' => $token]);
$user = $stmt->fetch();

// Проверка, найден ли пользователь
if ($user) {
    // Получаем document_number
    $documentNumber = $user['document_number'];

    // Запрос к таблице passengers для получения place_from и place_back (corrected)
    $stmt = $pdo->prepare("SELECT place_from, place_back FROM passengers WHERE document_number = :document_number");
    $stmt->execute(['document_number' => $documentNumber]);
    $passengerInfo = $stmt->fetch();

    // Проверка, найдены ли данные о пассажире
    if ($passengerInfo) {
        // Возврат данных о пассажире
        http_response_code(200);
        echo json_encode([
            "document_number" => $documentNumber,
            "place_from" => $passengerInfo['place_from'],
            "place_back" => $passengerInfo['place_back']
        ]);
    } else {
        // Если данные о пассажире не найдены
        http_response_code(404);
        echo json_encode(["error" => "Passenger information not found."]);
    }
} else {
    // Если пользователь не найден или токен неверный
    http_response_code(401);
    echo json_encode(["error" => "Invalid token."]);
}
?>