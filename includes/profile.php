<?php
require_once 'db.php';
require_once 'sessions.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$userStmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result()->fetch_assoc();

$blockStmt = $conn->prepare("
  SELECT u.user_id, u.username 
  FROM block_list b 
  JOIN users u ON b.blocked_id = u.user_id 
  WHERE b.blocker_id = ?
");
$blockStmt->bind_param("i", $user_id);
$blockStmt->execute();
$blockedUsers = $blockStmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
  'username' => $userResult['username'],
  'email' => $userResult['email'],
  'blocked_users' => $blockedUsers
]);

$userStmt->close();
$blockStmt->close();
$conn->close();
