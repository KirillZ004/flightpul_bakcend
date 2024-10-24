<?php  
  
$pdo = new PDO('mysql:host=localhost;dbname=flightpool;charset=utf8', 'root', null, [ PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);  
  
$formData = $_POST;  
$listErrors = [];  
$fields = ['first_name', 'last_name', 'phone', 'document_number', 'password'];  
  
// Задание 1: Проверяем, что все поля пришли  
foreach ($fields as $field) {  
    if (empty($formData[$field])) {  
        $listErrors[$field] = "$field: поле не заполнено";  
    }  
}  
  
// Задание 2: Проверяем, что такого пользователя не существует  
if (empty($listErrors)) {  
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");  
    $stmt->execute([$formData['phone']]);  
    $user = $stmt->fetch();  
  
    if ($user) {  
        $listErrors['phone'] = "Пользователь с таким номером телефона уже существует.";  
    }  
}  
  
// Если есть ошибки, отправляем их  
if (!empty($listErrors)) {  
    http_response_code(422);  
    echo json_encode([  
        "error" => [  
            "code" => 422,  
            "message" => "Validation error",  
            "errors" => $listErrors,  
        ]  
    ]);  
    exit;  
}  
  
// Задание 3: Записываем данные в таблицу users  
if (empty($listErrors)) {  
    http_response_code(204);  
  
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, phone, document_number, password) VALUES (?, ?, ?, ?, ?)");  
    $stmt->execute([  
        $formData['first_name'],   
        $formData['last_name'],   
        $formData['phone'],   
        $formData['document_number'],   
        $formData['password']  
    ]);  
}  
  
?>