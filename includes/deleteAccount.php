<?php
require_once 'sessions.php';
require_once 'db.php';
require_login();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  echo json_encode(["status" => "error", "message" => "User not found."]);
  exit;
}

try {
  // Start transaction
  $conn->begin_transaction();

  // Delete all messages sent or received by the user
  $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
  $stmt->bind_param("ii", $user_id, $user_id);
  $stmt->execute();

  // Delete from block list (both directions)
  $stmt = $conn->prepare("DELETE FROM block_list WHERE blocker_id = ? OR blocked_id = ?");
  $stmt->bind_param("ii", $user_id, $user_id);
  $stmt->execute();

  // Delete from typing_status
  $stmt = $conn->prepare("DELETE FROM typing_status WHERE sender_id = ? OR receiver_id = ?");
  $stmt->bind_param("ii", $user_id, $user_id);
  $stmt->execute();

  // Delete user account
  $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  // Commit transaction
  $conn->commit();

  // Destroy session
  session_destroy();

  echo json_encode(["status" => "success"]);
} catch (Exception $e) {
  $conn->rollback(); // roll back on error
  echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
