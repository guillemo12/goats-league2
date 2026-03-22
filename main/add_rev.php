<?php
require __DIR__ . '/db.php';
try {
    $pdo->exec("ALTER TABLE matches ADD COLUMN revenue_paid TINYINT(1) DEFAULT 0 AFTER status");
    echo "Added revenue_paid. \n";
} catch(Exception $e) { echo "Error or exists: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS team_finances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_id INT NOT NULL,
        match_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (team_id) REFERENCES teams(id),
        FOREIGN KEY (match_id) REFERENCES matches(id)
    )");
    echo "Created team_finances table.";
} catch(Exception $e) { echo "Error: " . $e->getMessage(); }
?>
