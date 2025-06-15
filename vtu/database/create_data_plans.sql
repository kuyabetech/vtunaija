CREATE TABLE data_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    network VARCHAR(32) NOT NULL,
    name VARCHAR(64) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    validity VARCHAR(32) NOT NULL,
    base_price DECIMAL(10,2) DEFAULT NULL,
    customer_price DECIMAL(10,2) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Example seed data
INSERT INTO data_plans (network, name, amount, validity, base_price, customer_price)
VALUES
('MTN', '1GB', 500, '30 days', 500, 500),
('MTN', '2GB', 1000, '30 days', 1000, 1000),
('MTN', '5GB', 2000, '30 days', 2000, 2000),
('GLO', '1.2GB', 500, '30 days', 500, 500),
('GLO', '2.9GB', 1000, '30 days', 1000, 1000),
('GLO', '5.8GB', 2000, '30 days', 2000, 2000),
('AIRTEL', '1GB', 500, '30 days', 500, 500),
('AIRTEL', '2GB', 1000, '30 days', 1000, 1000),
('AIRTEL', '5GB', 2000, '30 days', 2000, 2000),
('9MOBILE', '1GB', 500, '30 days', 500, 500),
('9MOBILE', '2.5GB', 1000, '30 days', 1000, 1000),
('9MOBILE', '5GB', 2000, '30 days', 2000, 2000);
