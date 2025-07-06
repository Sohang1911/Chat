<?php
require_once 'db.php';
require_once 'sessions.php';

$data = json_decode(file_get_contents("php://input"), true);

$receiver_id = intval($data['receiver_id'] ?? 0);
$is_typing = intval($data['is_typing'] ?? 0);
$sender_id = $_SESSION['user_id'] ?? 0;

if (!$sender_id || !$receiver_id) {
  echo json_encode(['status' => 'error', 'message' => 'Missing users']);
  exit;
}

$stmt = $conn->prepare("REPLACE INTO typing_status (sender_id, receiver_id, is_typing, updated_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iii", $sender_id, $receiver_id, $is_typing);
$stmt->execute();

echo json_encode(['status' => 'success']);
