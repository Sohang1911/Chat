<?php
require_once 'db.php';

header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');

if ($username === '') {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

echo json_encode(['exists' => $stmt->num_rows > 0]);

$stmt->close();
$conn->close();
