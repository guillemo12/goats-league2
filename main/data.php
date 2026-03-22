<?php
require_once __DIR__ . '/db.php';

$stmt = $pdo->query("SELECT id, name, points FROM teams ORDER BY points DESC");
$teams = $stmt->fetchAll();
?>