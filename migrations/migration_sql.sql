-- Add first_name and last_name columns to users table
ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT '';

-- Create user_image table
CREATE TABLE user_image (
  id INT AUTO_INCREMENT NOT NULL,
  user_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  uploaded_at DATETIME NOT NULL,
  is_profile TINYINT(1) NOT NULL DEFAULT 0,
  INDEX idx_user_id (user_id),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update doctrine_migration_versions to mark migration as executed
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) 
VALUES ('DoctrineMigrations\\Version20251229120000', NOW(), 0)
ON DUPLICATE KEY UPDATE executed_at = NOW();
