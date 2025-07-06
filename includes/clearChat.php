<?php
require_once 'db.php';
require_once 'sessions.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);
$receiver_id = $data['receiver_id'] ?? null;

if (!$user_id || !$receiver_id) {
  echo json_encode(['status' => 'error', 'message' => 'Missing user']);
  exit;
}

$stmt = $conn->prepare("
  DELETE FROM messages 
  WHERE (sender_id = ? AND receiver_id = ?) 
     OR (sender_id = ? AND receiver_id = ?)
");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);

if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Unable to clear chat']);
}

$stmt->close();
$conn->close();
