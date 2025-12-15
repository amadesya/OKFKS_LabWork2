# OKFKS_LabWork2

# Создание новой базы данных

СУБД — MySQL Workbench

- Скрипт для создания базы данных

```
CREATE DATABASE lab_security;

USE lab_security;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL
);

-- Добавляем пользователя для тестирования
INSERT INTO users (username, password, email) VALUES ('admin', 'password', 'example@mail.ru');

INSERT INTO users (username, password, email) VALUES ('user', 'password', 'other@mail.ru');


CREATE USER 'lab_security'@'localhost' identified with mysql_native_password BY 'lab_security';

GRANT ALL PRIVILEGES ON lab_security.* TO 'lab_security'@'localhost';
```

Скриншот созданной базы данных

<img width="165" height="115" alt="image" src="https://github.com/user-attachments/assets/55ba1ab2-2fd2-4ad6-90c4-0f1f41e66be6" />



