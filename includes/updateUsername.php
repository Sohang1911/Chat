<?php
require_once 'sessions.php';
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$newUsername = trim($data['username'] ?? '');

if ($newUsername === '') {
  echo json_encode(['status' => 'error', 'message' => 'Username cannot be empty']);
  exit;
}

// Check again in backend
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $newUsername);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(['status' => 'error', 'message' => 'Username already taken']);
  exit;
}
$stmt->close();

// Update username
$update = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
$update->bind_param("si", $newUsername, $_SESSION['user_id']);

if ($update->execute()) {
  $_SESSION['username'] = $newUsername;
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to update username']);
}

$update->close();
$conn->close();
