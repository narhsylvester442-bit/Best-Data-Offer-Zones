<?php
require_once "database.php";
session_start();

/* Allow admin only */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  http_response_code(403);
  exit(json_encode(["error" => "Unauthorized"]));
}

/* Profit summary */
$summary = $conn->query("
  SELECT 
    COUNT(*) as total_orders,
    SUM(selling_price - cost_price) as total_profit
  FROM transactions
")->fetch_assoc();

/* Daily chart data */
$chart = [];
$result = $conn->query("
  SELECT DATE(created_at) as day,
  SUM(selling_price - cost_price) as profit
  FROM transactions
  GROUP BY day
  ORDER BY day
");

while ($row = $result->fetch_assoc()) {
  $chart[] = $row;
}

echo json_encode([
  "summary" => $summary,
  "chart" => $chart
]);