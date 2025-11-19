CREATE DATABASE IF NOT EXISTS adornos;
USE adornos;

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    total_quantity INT NOT NULL DEFAULT 1,
    available_quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(255)
);

-- Users con contraseña en texto plano (OPCIÓN A)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- texto plano
    full_name VARCHAR(255),
    email VARCHAR(255),
    role ENUM('admin','department') NOT NULL DEFAULT 'department',
    department_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    dept_id INT NOT NULL,
    user_id INT NULL,
    quantity INT NOT NULL,
    status ENUM('reserved','picked_up','cancelled','returned') DEFAULT 'reserved',
    notes TEXT,
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    picked_up_at DATETIME NULL,
    returned_at DATETIME NULL,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NULL,
    item_id INT NOT NULL,
    dept_id INT NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    returned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    handled_by INT NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Usuario admin inicial (contraseña en texto plano: 'admin')
INSERT IGNORE INTO users (username, password, full_name, role)
VALUES ('admin', 'admin', 'Administrador', 'admin');
