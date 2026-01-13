<?php
// models.php
require_once "db.php";

class User {
    public static function findByEmail($email) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class Transaction {
    public static function create($userId, $amount, $type, $status) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status) VALUES (?,?,?,?)");
        $stmt->execute([$userId, $amount, $type, $status]);
        return $pdo->lastInsertId();
    }
}

class Wallet {
    public static function credit($userId, $amount) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
        return $stmt->execute([$amount, $userId]);
    }
}