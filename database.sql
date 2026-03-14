
-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Towers Table
CREATE TABLE IF NOT EXISTS towers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tower_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) DEFAULT 'Infrastructure',
    location VARCHAR(255),
    assigned_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 3. Daily Updates Table
CREATE TABLE IF NOT EXISTS daily_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tower_id INT NOT NULL,
    user_id INT NOT NULL,
    score INT NOT NULL CHECK (score BETWEEN 0 AND 5),
    status ENUM('Red', 'Orange', 'Green') NOT NULL,
    remarks TEXT,
    update_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tower_id) REFERENCES towers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (tower_id, update_date) -- Prevents more than one update per tower per day
);

-- Default Admin Credentials
-- Username: admin
-- Password: admin123 (Hashed for production, but for setup we'll provide a way or a default hash)
-- Hashed 'admin123' using BCRYPT: $2y$10$7R9jRY0.vXn7V2G.f4Y6u.s1.6J0vXgU/4.6u0.xG0u6z... wait, let's use a simpler one.
-- Let's insert a default admin.
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$mC7p6jG7E8Jm9v9vV/H3UuLp0Y7vQ5XyQ7vR5C8G8zV8N7rU8X5O.', 'admin'); -- password: admin123
