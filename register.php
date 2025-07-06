<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/sessions.php";

$registerStatus = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    if (empty($username) || empty($email) || empty($password)) {
        $registerStatus = "All fields are required.";
    } else {
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $registerStatus = "Username already exists.";
        } else {
            // Hash and insert
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                $conn->query("UPDATE users SET status = 'online' WHERE user_id = " . $_SESSION['user_id']);
                
                header("Location: index.html");
                exit();
            } else {
                $registerStatus = "❌ Something went wrong.";
            }

            $stmt->close();
        }

        $checkStmt->close();
    }

    $conn->close();
}
?>
