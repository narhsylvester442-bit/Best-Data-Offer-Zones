
<?php
$input = file_get_contents('php://input');
$data = [];
if ($input) {
  $json = json_decode($input, true);
  if (is_array($json)) $data = $json;
}
// Support form-encoded as well
if (empty($data)) $data = $_POST;

// Map network values to API expected codes
$network = isset($data['network']) ? $data['network'] : '';
switch ($network) {
  case 'AirtelTigo': $network = 'AT'; break;
  case 'Telecel': $network = 'TEL'; break;
  case 'MTN': $network = 'MTN'; break;
  case 'BIG': $network = 'BIG'; break;
}

$payload = [
  'network' => $network,
  'volume' => isset($data['volume']) ? (string)$data['volume'] : '',
  'customer_number' => isset($data['customer_number']) ? $data['customer_number'] : (isset($data['number']) ? $data['number'] : ''),
];
// Optional external reference
$payload['externalref'] = isset($data['externalref']) && $data['externalref'] !== '' ? $data['externalref'] : uniqid('api_', false);
$apiKey=getenv('DATAWAX_API_KEY');
if(!$apiKey){http_response_code(500);echo "Missing DATAWAX_API_KEY";exit;}
$ch=curl_init("https://datawax.site/wp-json/api/v1/place");
curl_setopt($ch,CURLOPT_HTTPHEADER,[
 "X-API-KEY: ".$apiKey,
 "Content-Type: application/json"
]);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload));
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
// Send Telegram alert on success
require_once __DIR__ . "/telegram.php";
if ($response) {
  $res = json_decode($response, true);
  if (is_array($res) && isset($res['status']) && intval($res['status']) === 1) {
    $msg = "Order created • ID {$res['order_id']} • {$res['network']} {$res['volume']} • {$res['number']}";
    sendTelegramAlert($msg);
  }
}
http_response_code($httpCode ?: 200);
header("Content-Type: application/json");
echo $response ?: json_encode(["status"=>0,"message"=>"No response"]);
?>
