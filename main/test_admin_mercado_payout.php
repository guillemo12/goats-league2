<?php
// Test and benchmark script for admin_mercado.php payout logic

$dbFile = tempnam(sys_get_temp_dir(), 'test_admin_mercado_') . '.sqlite';
putenv("DATABASE_URL=sqlite:" . $dbFile);

$pdo = new PDO("sqlite:" . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize schema
$pdo->exec("
    CREATE TABLE teams (id INTEGER PRIMARY KEY, name TEXT, budget REAL DEFAULT 0);
    CREATE TABLE matches (id INTEGER PRIMARY KEY, team1_id INTEGER, team2_id INTEGER, status TEXT, revenue_paid INTEGER DEFAULT 0);
    CREATE TABLE users (id INTEGER PRIMARY KEY, team_id INTEGER, name TEXT, role TEXT, profile_picture TEXT);
    CREATE TABLE match_ratings (id INTEGER PRIMARY KEY, match_id INTEGER, target_id INTEGER, rating REAL);
    CREATE TABLE team_finances (id INTEGER PRIMARY KEY, team_id INTEGER, match_id INTEGER, amount REAL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE settings (setting_key TEXT PRIMARY KEY, setting_value TEXT);
");

// Insert seed data
for ($i = 1; $i <= 10; $i++) {
    $pdo->exec("INSERT INTO teams (id, name, budget) VALUES ($i, 'Team $i', 1000.0)");
}

$uId = 1;
for ($t = 1; $t <= 10; $t++) {
    for ($p = 1; $p <= 5; $p++) {
        $pdo->exec("INSERT INTO users (id, team_id, name, role) VALUES ($uId, $t, 'Player $uId', 'user')");
        $uId++;
    }
}

// 50 matches
for ($m = 1; $m <= 50; $m++) {
    $t1 = (($m - 1) % 10) + 1;
    $t2 = ($m % 10) + 1;
    $pdo->exec("INSERT INTO matches (id, team1_id, team2_id, status, revenue_paid) VALUES ($m, $t1, $t2, 'finished', 0)");

    for ($p = 1; $p <= 5; $p++) {
        $p1 = ($t1 - 1) * 5 + $p;
        $pdo->exec("INSERT INTO match_ratings (match_id, target_id, rating) VALUES ($m, $p1, 8.5)");
        $p2 = ($t2 - 1) * 5 + $p;
        $pdo->exec("INSERT INTO match_ratings (match_id, target_id, rating) VALUES ($m, $p2, 7.5)");
    }
}

$pdo = null;

// Execute payout test by executing logic against SQLite DB
$pdo = new PDO("sqlite:" . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmtMatches = $pdo->query("SELECT id, team1_id, team2_id FROM matches WHERE status = 'finished' AND revenue_paid = 0");
$matches = $stmtMatches->fetchAll();
$payoutCount = 0;

if (!empty($matches)) {
    $matchIds = array_column($matches, 'id');
    $placeholders = implode(',', array_fill(0, count($matchIds), '?'));

    $stmtRatings = $pdo->prepare("
        SELECT mr.match_id, mr.target_id, u.team_id, AVG(mr.rating) as p_avg
        FROM match_ratings mr
        JOIN users u ON mr.target_id = u.id
        WHERE mr.match_id IN ($placeholders)
        GROUP BY mr.match_id, u.team_id, mr.target_id
    ");
    $stmtRatings->execute($matchIds);
    $allRatings = $stmtRatings->fetchAll();

    $teamMatchRatings = [];
    foreach ($allRatings as $r) {
        $mId = $r['match_id'];
        $tId = $r['team_id'];
        $teamMatchRatings[$mId][$tId][] = (float)$r['p_avg'];
    }

    $pdo->beginTransaction();
    try {
        $teamAdditions = [];
        $financesToInsert = [];
        $processedMatchIds = [];

        foreach ($matches as $match) {
            $mId = $match['id'];
            $t1 = $match['team1_id'];
            $t2 = $match['team2_id'];

            foreach ([$t1, $t2] as $tid) {
                if (isset($teamMatchRatings[$mId][$tid])) {
                    $ratings = $teamMatchRatings[$mId][$tid];
                    rsort($ratings);
                    $top = array_slice($ratings, 0, 7);

                    $tAvg = 0;
                    if (count($top) > 0) {
                        $tAvg = array_sum($top) / count($top);
                    }

                    if ($tAvg > 0) {
                        $teamAdditions[$tid] = ($teamAdditions[$tid] ?? 0) + $tAvg;
                        $financesToInsert[] = [$tid, $mId, $tAvg];
                    }
                }
            }
            $processedMatchIds[] = $mId;
            $payoutCount++;
        }

        if (!empty($teamAdditions)) {
            $updateTeamStmt = $pdo->prepare("UPDATE teams SET budget = budget + ? WHERE id = ?");
            foreach ($teamAdditions as $tid => $amount) {
                $updateTeamStmt->execute([$amount, $tid]);
            }
        }

        if (!empty($financesToInsert)) {
            $chunkSize = 100;
            $chunks = array_chunk($financesToInsert, $chunkSize);
            foreach ($chunks as $chunk) {
                $placeholders = implode(',', array_fill(0, count($chunk), '(?, ?, ?)'));
                $values = [];
                foreach ($chunk as $row) {
                    $values[] = $row[0];
                    $values[] = $row[1];
                    $values[] = $row[2];
                }
                $stmtInsert = $pdo->prepare("INSERT INTO team_finances (team_id, match_id, amount) VALUES $placeholders");
                $stmtInsert->execute($values);
            }
        }

        if (!empty($processedMatchIds)) {
            $mPlaceholders = implode(',', array_fill(0, count($processedMatchIds), '?'));
            $stmtUpdateMatches = $pdo->prepare("UPDATE matches SET revenue_paid = 1 WHERE id IN ($mPlaceholders)");
            $stmtUpdateMatches->execute($processedMatchIds);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        unlink($dbFile);
        echo "TEST FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Verification assertions
$unpaidMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE revenue_paid = 0")->fetchColumn();
$financesCount = $pdo->query("SELECT COUNT(*) FROM team_finances")->fetchColumn();

if ($unpaidMatches == 0 && $financesCount == 100 && $payoutCount == 50) {
    echo "SUCCESS: Payout batching verified successfully.\n";
    unlink($dbFile);
    exit(0);
} else {
    echo "TEST FAILED: Unpaid matches: $unpaidMatches, Finances count: $financesCount, Payout count: $payoutCount\n";
    unlink($dbFile);
    exit(1);
}
