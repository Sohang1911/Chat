<?php
require_once 'db.php';
require_once 'sessions.php';

$sender_id = intval($_GET['sender_id'] ?? 0);
$receiver_id = $_SESSION['user_id'] ?? 0;

if (!$sender_id || !$receiver_id) {
  echo json_encode(['is_typing' => false]);
  exit;
}

$sql = "SELECT is_typing, updated_at FROM typing_status 
        WHERE sender_id = ? AND receiver_id = ? AND updated_at > (NOW() - INTERVAL 5 SECOND)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $sender_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$is_typing = $row && $row['is_typing'] == 1;
echo json_encode(['is_typing' => $is_typing]);
