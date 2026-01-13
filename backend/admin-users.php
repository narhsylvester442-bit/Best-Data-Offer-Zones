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
   LIST USERS
========================= */
if ($action === "list") {

  $search = $_GET['search'] ?? '';
  $role   = $_GET['role'] ?? '';
  $status = $_GET['status'] ?? '';

  $where = "WHERE 1=1";
  $params = [];

  if ($search) {
    $where .= " AND (email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
  }

  if ($role) {
    $where .= " AND role=?";
    $params[] = $role;
  }

  if ($status === "approved") {
    $where .= " AND approved=1";
  }

  if ($status === "pending") {
    $where .= " AND approved=0";
  }

  $sql = $db->prepare("
    SELECT 
      id,
      email,
      phone,
      role,
      approved,
      wallet,
      created_at
    FROM users
    $where
    ORDER BY id DESC
  ");

  $sql->execute($params);
  echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/* =========================
   APPROVE USER
========================= */
if ($action === "approve") {

  $id = intval($_POST['id']);

  $sql = $db->prepare("UPDATE users SET approved=1 WHERE id=?");
  $sql->execute([$id]);

  echo "User approved";
  exit;
}

/* =========================
   SUSPEND USER
========================= */
if ($action === "suspend") {

  $id = intval($_POST['id']);

  $sql = $db->prepare("UPDATE users SET approved=0 WHERE id=?");
  $sql->execute([$id]);

  echo "User suspended";
  exit;
}

/* =========================
   PROMOTE TO SUBAGENT
========================= */
if ($action === "make_subagent") {

  $id = intval($_POST['id']);

  $sql = $db->prepare("
    UPDATE users 
    SET role='subagent'
    WHERE id=? AND role='agent'
  ");
  $sql->execute([$id]);

  echo "User promoted to sub-agent";
  exit;
}

/* =========================
   RESET USER WALLET (OPTIONAL)
========================= */
if ($action === "reset_wallet") {

  $id = intval($_POST['id']);

  $sql = $db->prepare("UPDATE users SET wallet=0 WHERE id=?");
  $sql->execute([$id]);

  echo "Wallet reset";
  exit;
}

echo "Invalid request";