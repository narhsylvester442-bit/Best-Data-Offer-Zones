<?php
// telegram.php

function sendTelegramAlert($message) {
    $token = getenv("TELEGRAM_BOT_TOKEN");
    $chatId = getenv("TELEGRAM_CHAT_ID");
    if(!$token || !$chatId) return false;

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = ["chat_id" => $chatId, "text" => $message];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response ?: false;
}
