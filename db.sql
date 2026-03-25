-- Создание таблицы заявок
CREATE TABLE IF NOT EXISTS application (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    fullname VARCHAR(150) NOT NULL COMMENT 'ФИО',
    phone VARCHAR(20) NOT NULL COMMENT 'Телефон',
    email VARCHAR(100) NOT NULL COMMENT 'Email',
    birthdate DATE NOT NULL COMMENT 'Дата рождения',
    gender ENUM('male', 'female', 'other') NOT NULL COMMENT 'Пол',
    bio TEXT COMMENT 'Биография',
    contract_agreed TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Согласие с контрактом',
    created_at DATETIME NOT NULL COMMENT 'Дата создания',
    PRIMARY KEY (id),
    INDEX idx_email (email),
    INDEX idx_birthdate (birthdate),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица для хранения выбранных языков
CREATE TABLE IF NOT EXISTS application_languages (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id INT(10) UNSIGNED NOT NULL COMMENT 'ID заявки',
    language_name VARCHAR(50) NOT NULL COMMENT 'Название языка',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (application_id) REFERENCES application(id) ON DELETE CASCADE,
    INDEX idx_application (application_id),
    INDEX idx_language (language_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;