<?php
session_start();
require "db.php";

/*
  COMMISSION LOGIC
  ----------------
  - Agents earn commission from their sub-agents
  - Sub-agents earn commission from their own sales
  - Commission stored per transaction
*/

if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = intval($_SESSION['uid']);

/*
 Table expected:
 commissions(
   id,
   user_id,
   amount,
   source,
   created_at
 )
*/

/* Get total commission */
$stmt = $conn->prepare("
    SELECT IFNULL(SUM(amount),0) AS total
    FROM commissions
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

/* Optional: recent commission history */
$history = [];
$h = $conn->prepare("
    SELECT amount, source, created_at
    FROM commissions
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 10
");
$h->bind_param("i", $user_id);
$h->execute();
$r = $h->get_result();
while ($c = $r->fetch_assoc()) {
    $history[] = $c;
}

echo json_encode([
    "total" => number_format($row['total'], 2),
    "history" => $history
]);