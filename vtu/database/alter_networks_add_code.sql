ALTER TABLE networks
ADD COLUMN code VARCHAR(16) NULL AFTER id;

-- Update each row with a unique code (example codes)
UPDATE networks SET code = 'MTN' WHERE name = 'MTN';
UPDATE networks SET code = 'GLO' WHERE name = 'GLO';
UPDATE networks SET code = 'AIRTEL' WHERE name = 'AIRTEL';
UPDATE networks SET code = '9MOBILE' WHERE name = '9MOBILE';

-- Now enforce NOT NULL and UNIQUE
ALTER TABLE networks
MODIFY COLUMN code VARCHAR(16) NOT NULL UNIQUE;
