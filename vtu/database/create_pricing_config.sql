CREATE TABLE pricing_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    network VARCHAR(32) NOT NULL,
    service_type VARCHAR(32) NOT NULL,
    min_amount DECIMAL(10,2) DEFAULT NULL,
    max_amount DECIMAL(10,2) DEFAULT NULL,
    markup_type ENUM('flat','percent') NOT NULL DEFAULT 'flat',
    markup_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Example seed data
INSERT INTO pricing_config (network, service_type, min_amount, max_amount, markup_type, markup_value)
VALUES
('MTN', 'data', 0, 10000, 'percent', 2.5),
('GLO', 'data', 0, 10000, 'percent', 2.0),
('AIRTEL', 'data', 0, 10000, 'percent', 2.0),
('9MOBILE', 'data', 0, 10000, 'percent', 2.0),
('MTN', 'airtime', 0, 10000, 'flat', 10),
('GLO', 'airtime', 0, 10000, 'flat', 10),
('AIRTEL', 'airtime', 0, 10000, 'flat', 10),
('9MOBILE', 'airtime', 0, 10000, 'flat', 10);
