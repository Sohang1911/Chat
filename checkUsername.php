<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['available' => false, 'error' => 'Not logged in']);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');

if ($username === '') {
    echo json_encode(['available' => false, 'error' => 'Empty username']);
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Check if the username is taken by another user
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
$stmt->bind_param("si", $username, $currentUserId);
$stmt->execute();
$stmt->store_result();

$isTaken = $stmt->num_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['available' => !$isTaken]);
?>
