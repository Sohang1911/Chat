<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.html");
        exit();
    }
}

// New: logout function
function logout() {
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        require_once "includes/db.php";
        $conn->query("UPDATE users SET status = 'offline' WHERE user_id = $user_id");
    }

    session_destroy();
    header("Location: login.html");
    exit();
    }
?>
