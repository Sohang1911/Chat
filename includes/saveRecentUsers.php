<?php
require_once 'includes/db.php';
require_once 'includes/sessions.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$me = $_SESSION['user_id'];
$other = intval($data['user_id']);
if ($other === $me) { echo json_encode(['status'=>'error']); exit; }

$stmt = $conn->prepare("
  INSERT INTO recent_chats (user_id, chat_user_id)
  VALUES (?, ?)
  ON DUPLICATE KEY UPDATE last_chat = CURRENT_TIMESTAMP
");
$stmt->bind_param("ii", $me, $other);
$stmt->execute();

echo json_encode(['status' => 'ok']);
