<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/sessions.php";

$loginStatus = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);

    if (empty($username) || empty($password)) {
        $loginStatus = "Please enter both fields.";
    } else {
    $stmt = $conn->prepare("SELECT user_id, username, email, password, profile_pic FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $db_username, $email, $hashed_password, $profile_pic);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $db_username;
                $_SESSION['email'] = $email;
                $_SESSION['profile_pic'] = $profile_pic;

                
                $conn->query("UPDATE users SET status = 'online' WHERE user_id = " . $_SESSION['user_id']);

                header("Location: index.html");
                exit();
            } else {
                header("Location: login.html?error=invalid_password");
                exit();
            }
        } else {
            header("Location: login.html?error=user_not_found");
            exit();
        }

    }

    $conn->close();
}
?>
