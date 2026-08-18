<?php
// main/test_tratos_benchmark.php

function setupDatabase() {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE teams (id INTEGER PRIMARY KEY, name TEXT)");
    $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, team_id INTEGER)");
    $pdo->exec("CREATE TABLE matches (id INTEGER PRIMARY KEY, team1_id INTEGER, team2_id INTEGER, status TEXT, match_date TEXT)");
    $pdo->exec("CREATE TABLE match_ratings (id INTEGER PRIMARY KEY, match_id INTEGER, voter_id INTEGER, target_id INTEGER, rating REAL)");

    $pdo->exec("INSERT INTO teams (id, name) VALUES (1, 'Team A'), (2, 'Team B')");

    // Insert 10 players for Team 1
    $stmtUser = $pdo->prepare("INSERT INTO users (id, name, team_id) VALUES (?, ?, 1)");
    for ($i = 1; $i <= 10; $i++) {
        $stmtUser->execute([$i, "Player $i"]);
    }

    // Insert 10 finished matches
    $stmtMatch = $pdo->prepare("INSERT INTO matches (id, team1_id, team2_id, status, match_date) VALUES (?, 1, 2, 'finished', ?)");
    for ($m = 1; $m <= 10; $m++) {
        $stmtMatch->execute([$m, "2026-01-0$m"]);
    }

    // Insert match ratings (e.g. 3 voters rating each of the 10 players in each match)
    $stmtRating = $pdo->prepare("INSERT INTO match_ratings (match_id, voter_id, target_id, rating) VALUES (?, ?, ?, ?)");
    for ($m = 1; $m <= 10; $m++) {
        for ($p = 1; $p <= 10; $p++) {
            for ($v = 100; $v <= 102; $v++) {
                // Randomish ratings between 5.0 and 10.0
                $rating = 5.0 + (($m * 7 + $p * 3 + $v) % 50) / 10.0;
                $stmtRating->execute([$m, $v, $p, $rating]);
            }
        }
    }

    return $pdo;
}

function calculateTeamRatingOld($pdo, $myTeamId, $finishedMatches) {
    $teamRating = 0;
    if (count($finishedMatches) > 0) {
        $matchAvgs = [];
        foreach ($finishedMatches as $fm) {
            $stmtTop7 = $pdo->prepare("SELECT AVG(rating) FROM match_ratings WHERE match_id = ? AND target_id IN (SELECT id FROM users WHERE team_id = ?) GROUP BY target_id ORDER BY AVG(rating) DESC LIMIT 7");
            $stmtTop7->execute([$fm['id'], $myTeamId]);
            $topRatings = $stmtTop7->fetchAll(PDO::FETCH_COLUMN);
            if (count($topRatings) > 0) $matchAvgs[] = array_sum($topRatings) / count($topRatings);
        }
        if (count($matchAvgs) > 0) $teamRating = array_sum($matchAvgs) / count($matchAvgs);
    }
    return $teamRating;
}

function calculateTeamRatingNew($pdo, $myTeamId, $finishedMatches) {
    $teamRating = 0;
    if (count($finishedMatches) > 0) {
        $matchIds = array_column($finishedMatches, 'id');
        $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
        $sql = "SELECT mr.match_id, mr.target_id, AVG(mr.rating) as avg_rating
                FROM match_ratings mr
                JOIN users u ON mr.target_id = u.id
                WHERE mr.match_id IN ($placeholders) AND u.team_id = ?
                GROUP BY mr.match_id, mr.target_id";
        $params = array_merge($matchIds, [$myTeamId]);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ratingsByMatch = [];
        foreach ($rows as $row) {
            $ratingsByMatch[$row['match_id']][] = (float)$row['avg_rating'];
        }

        $matchAvgs = [];
        foreach ($finishedMatches as $fm) {
            $mId = $fm['id'];
            if (isset($ratingsByMatch[$mId])) {
                $playerRatings = $ratingsByMatch[$mId];
                rsort($playerRatings);
                $top7 = array_slice($playerRatings, 0, 7);
                if (count($top7) > 0) {
                    $matchAvgs[] = array_sum($top7) / count($top7);
                }
            }
        }
        if (count($matchAvgs) > 0) $teamRating = array_sum($matchAvgs) / count($matchAvgs);
    }
    return $teamRating;
}

$pdo = setupDatabase();
$myTeamId = 1;

$stmtTeamMatches = $pdo->prepare("SELECT id FROM matches WHERE (team1_id = ? OR team2_id = ?) AND status = 'finished' ORDER BY match_date DESC LIMIT 10");
$stmtTeamMatches->execute([$myTeamId, $myTeamId]);
$finishedMatches = $stmtTeamMatches->fetchAll(PDO::FETCH_ASSOC);

$oldResult = calculateTeamRatingOld($pdo, $myTeamId, $finishedMatches);
$newResult = calculateTeamRatingNew($pdo, $myTeamId, $finishedMatches);

echo "Old Result: " . $oldResult . "\n";
echo "New Result: " . $newResult . "\n";

if (abs($oldResult - $newResult) > 0.00001) {
    echo "ERROR: Results do not match!\n";
    exit(1);
} else {
    echo "SUCCESS: Results match perfectly!\n";
}

// Benchmarking
$iterations = 1000;

$startOld = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    calculateTeamRatingOld($pdo, $myTeamId, $finishedMatches);
}
$endOld = hrtime(true);
$durationOld = ($endOld - $startOld) / 1e6; // ms

$startNew = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    calculateTeamRatingNew($pdo, $myTeamId, $finishedMatches);
}
$endNew = hrtime(true);
$durationNew = ($endNew - $startNew) / 1e6; // ms

echo "\n--- Benchmark Results ($iterations iterations) ---\n";
echo sprintf("Old Approach: %.2f ms (%.4f ms/op)\n", $durationOld, $durationOld / $iterations);
echo sprintf("New Approach: %.2f ms (%.4f ms/op)\n", $durationNew, $durationNew / $iterations);
$speedup = ($durationOld - $durationNew) / $durationOld * 100;
echo sprintf("Improvement: %.2f%% faster\n", $speedup);
