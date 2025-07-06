<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$current = $data['current'] ?? '';
$newPass = $data['newPass'] ?? '';

if (!$current || !$newPass) {
  echo json_encode(['status' => 'error', 'message' => 'Missing data']);
  exit;
}

$userId = $_SESSION['user_id'];

// Get current password hash
$stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

// Verify
if (!password_verify($current, $hash)) {
  echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
  exit;
}

// Update password
$newHash = password_hash($newPass, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$update->bind_param("si", $newHash, $userId);

if ($update->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}

$update->close();
$conn->close();
