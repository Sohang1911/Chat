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

$stmt = $conn->prepare("REPLACE INTO block_list (blocker_id, blocked_id, blocked_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $blocker_id, $blocked_id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Could not block']);
}

$stmt->close();
$conn->close();
