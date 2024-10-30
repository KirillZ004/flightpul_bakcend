<?php 
// Подключение к базе данных
$pdo = new PDO('mysql:host=localhost;dbname=flightpool;charset=utf8', 'root', null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Получение данных из тела запроса
$formData = json_decode(file_get_contents("php://input"), true);

// Проверка: переданы ли необходимые данные
if (!isset($formData['phone']) || !isset($formData['password'])) {
    http_response_code(422);
    echo json_encode([
        "error" => [
            "code" => 422,
            "message" => "Validation error",
            "errors" => [
                "phone" => ["телефон треба"],
                "password" => ["Пароль треба"]
            ]
        ]
    ]);
    exit;
}

// Получение данных из формы
$phone = $formData['phone'];
$password = $formData['password'];

// Поиск пользователя в базе данных
$stmt = $pdo->prepare("SELECT * FROM users WHERE phone = :phone AND password = :password");
$stmt->execute(['phone' => $phone, 'password' => $password]);
$user = $stmt->fetch();

if ($user) {
    // Генерация токена
    $token = base64_encode("username={$user['first_name']}&password={$password}");
    
    // Сохранение токена в базе данных
    $updateStmt = $pdo->prepare("UPDATE users SET api_token = :token WHERE id = :id");
    $updateStmt->execute(['token' => $token, 'id' => $user['id']]);
    
    // Успешный ответ
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        "data" => [
            "token" => $token
        ]
    ]);
} else {
    // Если данные неверны
    http_response_code(401);
    echo json_encode([
        "error" => [
            "code" => 401,
            "message" => "Не авторизован",
            "errors" => [
                "phone" => ["Пароль или телефон не верны"]
            ]
        ]
    ]);
}
?>
