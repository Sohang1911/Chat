<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

$current_id = $_SESSION['user_id'] ?? null;
if (!$current_id) {
  echo json_encode(['users' => []]);
  exit;
}

$stmt = $conn->prepare("SELECT DISTINCT u.user_id, u.username
  FROM users u
  JOIN messages m ON (u.user_id = m.sender_id OR u.user_id = m.receiver_id)
  WHERE u.user_id != ? AND (m.sender_id = ? OR m.receiver_id = ?)
  ORDER BY u.username");

$stmt->bind_param("iii", $current_id, $current_id, $current_id);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['users' => $users]);

$stmt->close();
$conn->close();
?>