<?php
// referrals.php
require_once "models.php";

function awardCommission($agentId, $amount) {
    $commission = $amount * 0.05; // 5% commission
    Wallet::credit($agentId, $commission);
    Transaction::create($agentId, $commission, "commission", "completed");
}