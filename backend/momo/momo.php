<?php
session_start();
require_once "database.php";

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
| Replace later with real MTN MoMo / Hubtel credentials
*/
define("MOMO_ENV", "sandbox"); // sandbox | live
define("MOMO_CURRENCY", "GHS");

/*
|--------------------------------------------------------------------------
| AUTH CHECK (Wallet funding requires login)
|--------------------------------------------------------------------------
*/
if(!isset($_SESSION['user_id'])){
  http_response_code(401);
  exit("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$action  = $_GET['action'] ?? "";

/*
|--------------------------------------------------------------------------
| INITIATE WALLET FUNDING
|--------------------------------------------------------------------------
*/
if($action === "initiate"){

  $amount = floatval($_POST['amount']);
  $phone  = trim($_POST['phone']);

  if($amount <= 0){
    exit("Invalid amount");
  }

  if(strlen($phone) < 10){
    exit("Invalid phone number");
  }

  /* Create local transaction reference */
  $reference = uniqid("momo_");

  /* Save pending transaction */
  $stmt = $db->prepare("
    INSERT INTO momo_transactions(user_id,amount,phone,reference,status)
    VALUES(?,?,?,?, 'pending')
  ");
  $stmt->execute([$user_id,$amount,$phone,$reference]);

  /*
  |--------------------------------------------------------------------------
  | HERE YOU CALL YOUR MTN MOMO API
  |--------------------------------------------------------------------------
  | Example (pseudo):
  | sendMomoRequest($phone,$amount,$reference);
  */

  echo json_encode([
    "status" => "pending",
    "reference" => $reference,
    "message" => "Approve payment on your phone"
  ]);
  exit;
}

/*
|--------------------------------------------------------------------------
| VERIFY PAYMENT (POLLING / CALLBACK)
|--------------------------------------------------------------------------
*/
if($action === "verify"){

  $reference = $_GET['reference'] ?? "";

  $stmt = $db->prepare("
    SELECT * FROM momo_transactions
    WHERE reference=?
  ");
  $stmt->execute([$reference]);
  $tx = $stmt->fetch(PDO::FETCH_ASSOC);

  if(!$tx){
    exit("Transaction not found");
  }

  /*
  |--------------------------------------------------------------------------
  | Replace this block with real API verification
  |--------------------------------------------------------------------------
  */
  if($tx['status'] === "pending"){
    // Simulate success (sandbox)
    $db->beginTransaction();

    $db->prepare("
      UPDATE momo_transactions
      SET status='success'
      WHERE id=?
    ")->execute([$tx['id']]);

    $db->prepare("
      UPDATE users
      SET wallet = wallet + ?
      WHERE id=?
    ")->execute([$tx['amount'],$tx['user_id']]);

    $db->commit();
  }

  echo json_encode([
    "status" => "success",
    "amount" => $tx['amount']
  ]);
  exit;
}

/*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
*/
echo "Invalid request";