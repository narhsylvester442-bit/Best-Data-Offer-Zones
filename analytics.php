<?php
// analytics.php
require_once "db.php";

header("Content-Type: application/json");

$stmt = $pdo->query("SELECT network, COUNT(*) as count FROM transactions GROUP BY network");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
