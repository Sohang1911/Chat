<?php
$host = 'localhost';
$user = 'root';         // Change if your MySQL user is different
$password = '';         // Add password if you use one
$database = 'chat_system';  // Make sure this matches your database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
