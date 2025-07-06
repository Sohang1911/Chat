<?php
require_once 'db.php';
require_once 'sessions.php';
header('Content-Type: application/json');

$blocker_id = $_SESSION['user_id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);
$blocked_id = $data['blocked_id'] ?? null;

if (!$blocker_id || !$blocked_id) {
  echo json_encode(['status' => 'error', 'message' => 'Missing data']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM block_list WHERE blocker_id = ? AND blocked_id = ?");
$stmt->bind_param("ii", $blocker_id, $blocked_id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to unblock']);
}

$stmt->close();
$conn->close();
