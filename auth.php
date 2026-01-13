<?php
session_start();
require_once "models.php";

function signup($email, $password) {
    global $pdo;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?,?, 'customer')");
    return $stmt->execute([$email, $hash]);
}

function login($email, $password) {
    $user = User::findByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return $user;
    }
    return false;
}
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'login') {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $user = login($email, $password);
        if ($user) {
            $role = isset($user['role']) ? $user['role'] : 'agent';
            echo $role;
        } else {
            echo "Denied";
        }
        exit;
    }
    if ($action === 'session') {
        $logged = isset($_SESSION['user_id']);
        header("Content-Type: application/json");
        echo json_encode(["logged_in" => $logged]);
        exit;
    }
}
