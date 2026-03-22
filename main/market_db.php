<?php
require __DIR__ . '/db.php';

try {
    $pdo->exec("ALTER TABLE teams ADD COLUMN budget DECIMAL(10,2) DEFAULT 0.00 AFTER name");
    echo "Added budget to teams.\n";
} catch(PDOException $e) { echo "budget already exists or error: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN is_protected TINYINT(1) DEFAULT 0 AFTER role");
    echo "Added is_protected to users.\n";
} catch(PDOException $e) { echo "is_protected already exists or error: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(255)
    )");
    
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'market_open'");
    if(!$stmt->fetch()) {
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('market_open', '0')");
    }
    echo "Settings table ready.\n";
} catch(PDOException $e) { echo "settings table error: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS transfers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        buyer_team_id INT NOT NULL,
        player_id INT NOT NULL,
        seller_team_id INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (buyer_team_id) REFERENCES teams(id),
        FOREIGN KEY (player_id) REFERENCES users(id),
        FOREIGN KEY (seller_team_id) REFERENCES teams(id)
    )");
    echo "Transfers table ready.\n";
} catch(PDOException $e) { echo "transfers table error: " . $e->getMessage() . "\n"; }

echo "Market DB setup complete.\n";
?>
