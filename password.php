
<?php

// Подключаемся к базе данных
$pdo = new PDO('mysql:host=localhost;dbname=flightpool;charset=utf8', 'root', null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Получаем данные из заголовков и тела запроса
$headers = getallheaders();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : null;
$formData = json_decode(file_get_contents("php://input"), true);
$newPassword = isset($formData['password']) ? $formData['password'] : null;

// Проверка: передан ли токен
if (!$token) {
    http_response_code(401);
    echo json_encode([
        "error" => [
            "code" => 401,
            "message" => "Unauthorized - Token not provided"
        ]
    ]);
    exit;
}

// Проверка: передан ли новый пароль
if (!$newPassword) {
    http_response_code(422);
    echo json_encode([
        "error" => [
            "code" => 422,
            "message" => "Validation error",
            "errors" => ["password" => "Password field is required"]
        ]
    ]);
    exit;
}

// Находим пользователя по токену
$stmt = $pdo->prepare('SELECT id FROM users WHERE api_token = :token');
$stmt->execute(['token' => $token]);
$user = $stmt->fetch();

// Проверка: существует ли пользователь с таким токеном
if (!$user) {
    http_response_code(404);
    echo json_encode([
        "error" => [
            "code" => 404,
            "message" => "User not found"
        ]
    ]);
    exit;
}

// Обновляем пароль пользователя
$stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
$stmt->execute([
    'password' => $newPassword,
    'id' => $user['id']
]);

// Возвращаем статус успешного выполнения
http_response_code(200);
echo json_encode([
    "message" => "Password updated successfully"
]);

?>