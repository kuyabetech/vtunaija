ALTER TABLE wallet_transactions
ADD COLUMN bank VARCHAR(64) DEFAULT NULL AFTER payment_method;
