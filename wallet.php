<?php
// wallet.php
require_once "models.php";

function momoRequestToPay($userId, $amount, $phone) {
    $payload = [
        "amount" => $amount,
        "currency" => "GHS",
        "customer_number" => $phone
    ];

    $ch = curl_init("https://mtn-momo-api/request-to-pay");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer YOUR_MOMO_TOKEN",
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    Transaction::create($userId, $amount, "wallet_funding", "pending");
    return $response;
}