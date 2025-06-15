CREATE TABLE networks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(32) NOT NULL UNIQUE,
    display_name VARCHAR(64) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Example seed data
INSERT INTO networks (name, display_name) VALUES
('MTN', 'MTN Nigeria'),
('GLO', 'Glo Nigeria'),
('AIRTEL', 'Airtel Nigeria'),
('9MOBILE', '9mobile Nigeria');
