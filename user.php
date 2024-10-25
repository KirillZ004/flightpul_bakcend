<?php

// Подключение к базе данных
$pdo = new PDO('mysql:host=localhost;dbname=flightpool;charset=utf8', 'root', null, [ PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

// Получаем токен из заголовков запроса
$headers = getallheaders();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

// Переменная для хранения информации о пользователе
$user = [];

// Проверяем наличие токена
if ($token) {
    // Выполняем запрос в базу данных, чтобы найти пользователя по api_token
    $stmt = $pdo->prepare('SELECT first_name, last_name, phone, document_number FROM users WHERE api_token = :token');
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();
}

// Если пользователь найден, возвращаем его данные
if (!empty($user)) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    // Если api_token не найден или пользователь не существует, возвращаем ошибку 401
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        "error" => [
            "code" => 401,
            "message" => "Unauthorized"
        ]
    ]);
}

?>
