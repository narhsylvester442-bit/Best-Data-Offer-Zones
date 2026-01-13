<?php
session_start();
require_once "database.php";

/* =========================
   ADMIN AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
  http_response_code(403);
  exit("Unauthorized");
}

$action = $_GET['action'] ?? "";

/* =========================
   LIST WITHDRAWALS
========================= */
if ($action === "list") {

  $status = $_GET['status'] ?? '';
  $search = $_GET['search'] ?? '';

  $where = "WHERE 1=1";
  $params = [];

  if ($status) {
    $where .= " AND w.status=?";
    $params[] = $status;
  }

  if ($search) {
    $where .= " AND (u.email LIKE ? OR w.momo_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
  }

  $sql = $db->prepare("
    SELECT 
      w.id,
      u.email,
      u.role,
      w.amount,
      w.momo_number,
      w.status,
      w.created_at
    FROM withdrawals w
    JOIN users u ON w.user_id = u.id
    $where
    ORDER BY w.id DESC
  ");

  $sql->execute($params);
  echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/* =========================
   APPROVE WITHDRAWAL
========================= */
if ($action === "approve") {

  $id = intval($_POST['id']);

  $sql = $db->prepare("
    SELECT * FROM withdrawals
    WHERE id=? AND status='pending'
  ");
  $sql->execute([$id]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    exit("Invalid or already processed request");
  }

  $db->prepare("
    UPDATE withdrawals
    SET status='approved'
    WHERE id=?
  ")->execute([$id]);

  /*
  |--------------------------------------------------------------------------
  | OPTIONAL: Trigger MTN MoMo payout here
  |--------------------------------------------------------------------------
  | sendMomoPayout($row['momo_number'], $row['amount']);
  */

  echo "Withdrawal approved";
  exit;
}

/* =========================
   REJECT WITHDRAWAL
========================= */
if ($action === "reject") {

  $id = intval($_POST['id']);

  $sql = $db->prepare("
    SELECT * FROM withdrawals
    WHERE id=? AND status='pending'
  ");
  $sql->execute([$id]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    exit("Invalid or already processed request");
  }

  $db->beginTransaction();

  /* Update status */
  $db->prepare("
    UPDATE withdrawals
    SET status='rejected'
    WHERE id=?
  ")->execute([$id]);

  /* Refund wallet */
  $db->prepare("
    UPDATE users
    SET wallet = wallet + ?
    WHERE id=?
  ")->execute([$row['amount'], $row['user_id']]);

  $db->commit();

  echo "Withdrawal rejected and wallet refunded";
  exit;
}

echo "Invalid request";