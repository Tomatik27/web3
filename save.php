<?php
header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения для u82279
$host = 'localhost';
$dbname = 'u82279';
$user = 'u82279';
$pass = '4483607';  // Вставьте свой пароль

function showError($message) {
    echo '<div class="error-message">❌ ' . htmlspecialchars($message) . '</div>';
    echo '<a href="index.html" class="back-link">← Вернуться к форме</a>';
    exit();
}

function showSuccess($message) {
    echo '<div class="success-message">✅ ' . htmlspecialchars($message) . '</div>';
    echo '<a href="index.html" class="back-link">← Вернуться к форме</a>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showError('Некорректный метод запроса');
}

$errors = [];

// 1. Валидация ФИО
$fullname = trim($_POST['fullname'] ?? '');
if (empty($fullname)) {
    $errors['fullname'] = 'Поле ФИО обязательно для заполнения';
} elseif (strlen($fullname) > 150) {
    $errors['fullname'] = 'ФИО не должно превышать 150 символов';
} elseif (!preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $fullname)) {
    $errors['fullname'] = 'ФИО должно содержать только буквы и пробелы';
}

// 2. Валидация телефона
$phone = trim($_POST['phone'] ?? '');
if (empty($phone)) {
    $errors['phone'] = 'Поле Телефон обязательно для заполнения';
} elseif (!preg_match('/^[\+0-9\s\(\)\-]{10,20}$/', $phone)) {
    $errors['phone'] = 'Введите корректный номер телефона';
}

// 3. Валидация email
$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    $errors['email'] = 'Поле E-mail обязательно для заполнения';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите корректный email адрес';
}

// 4. Валидация даты рождения
$birthdate = $_POST['birthdate'] ?? '';
if (empty($birthdate)) {
    $errors['birthdate'] = 'Поле Дата рождения обязательно для заполнения';
} else {
    $dateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $birthdate) {
        $errors['birthdate'] = 'Некорректный формат даты';
    } else {
        $today = new DateTime();
        $age = $today->diff($dateObj)->y;
        if ($age < 14 || $age > 100) {
            $errors['birthdate'] = 'Возраст должен быть от 14 до 100 лет';
        }
    }
}

// 5. Валидация пола
$gender = $_POST['gender'] ?? '';
$allowedGenders = ['male', 'female', 'other'];
if (empty($gender)) {
    $errors['gender'] = 'Поле Пол обязательно для заполнения';
} elseif (!in_array($gender, $allowedGenders)) {
    $errors['gender'] = 'Выбран недопустимый вариант пола';
}

// 6. Валидация языков (список допустимых)
$allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
$languages = $_POST['languages'] ?? [];
if (empty($languages)) {
    $errors['languages'] = 'Выберите хотя бы один язык программирования';
} else {
    foreach ($languages as $lang) {
        if (!in_array($lang, $allowedLanguages)) {
            $errors['languages'] = 'Выбран недопустимый язык программирования';
            break;
        }
    }
}

// 7. Валидация биографии
$bio = trim($_POST['bio'] ?? '');
if (strlen($bio) > 1000) {
    $errors['bio'] = 'Биография не должна превышать 1000 символов';
}

// 8. Валидация чекбокса
$contractAgreed = $_POST['contract_agreed'] ?? '';
if (empty($contractAgreed) || $contractAgreed !== '1') {
    $errors['contract_agreed'] = 'Необходимо подтвердить ознакомление с контрактом';
}

if (!empty($errors)) {
    echo '<div class="error-container">';
    echo '<h2>Ошибки валидации:</h2>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '<a href="index.html" class="back-link">← Вернуться к форме</a>';
    echo '</div>';
    exit();
}

// Сохранение в БД
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $pdo->beginTransaction();
    
    // 1. Вставляем в таблицу application
    $stmt = $pdo->prepare("
        INSERT INTO application (fullname, phone, email, birthdate, gender, bio, contract_agreed, created_at)
        VALUES (:fullname, :phone, :email, :birthdate, :gender, :bio, :contract_agreed, NOW())
    ");
    
    $stmt->execute([
        ':fullname' => $fullname,
        ':phone' => $phone,
        ':email' => $email,
        ':birthdate' => $birthdate,
        ':gender' => $gender,
        ':bio' => $bio,
        ':contract_agreed' => $contractAgreed
    ]);
    
    // Получаем ID последней вставленной записи
    $applicationId = $pdo->lastInsertId();
    
    // 2. Вставляем выбранные языки в таблицу application_languages
    $langStmt = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_name)
        VALUES (:application_id, :language_name)
    ");
    
    foreach ($languages as $language) {
        $langStmt->execute([
            ':application_id' => $applicationId,
            ':language_name' => $language
        ]);
    }
    
    $pdo->commit();
    showSuccess('Данные успешно сохранены!');
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    showError('Ошибка при сохранении данных');
}
?>