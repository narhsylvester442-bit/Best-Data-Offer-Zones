<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Access denied");
}

$where = "1";

if (!empty($_GET['network'])) {
    $net = $conn->real_escape_string($_GET['network']);
    $where .= " AND network='$net'";
}

if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where .= " AND status='$status'";
}

if (!empty($_GET['date'])) {
    $date = $conn->real_escape_string($_GET['date']);
    $where .= " AND DATE(created_at)='$date'";
}

$q = "
SELECT network, volume, number, amount, profit, status, created_at
FROM transactions
WHERE $where
ORDER BY id DESC
LIMIT 200
";

$r = $conn->query($q);

echo "<table>
<tr>
<th>Network</th>
<th>Volume</th>
<th>Number</th>
<th>Amount</th>
<th>Profit</th>
<th>Status</th>
<th>Date</th>
</tr>";

while ($row = $r->fetch_assoc()) {
    echo "<tr>
      <td>{$row['network']}</td>
      <td>{$row['volume']}GB</td>
      <td>{$row['number']}</td>
      <td>GHS {$row['amount']}</td>
      <td>GHS {$row['profit']}</td>
      <td>{$row['status']}</td>
      <td>{$row['created_at']}</td>
    </tr>";
}

echo "</table>";