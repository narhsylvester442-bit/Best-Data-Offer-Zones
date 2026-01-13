<?php
// data-api.php
require_once "models.php";

function buyData($userId, $network, $volume, $customerNumber) {
    $payload = [
        "network" => $network,
        "volume" => $volume,
        "customer_number" => $customerNumber
    ];
    $apiKey=getenv('DATAWAX_API_KEY');
    if(!$apiKey){return json_encode(["error"=>"Missing DATAWAX_API_KEY"]);}

    $ch = curl_init("https://datawax.site/wp-json/api/v1/place");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "X-API-KEY: ".$apiKey,
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    Transaction::create($userId, $volume, "bundle_purchase", "pending");
    return $response;
}
