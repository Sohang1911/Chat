<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');
$current_id = $_SESSION['user_id'] ?? null;

if (!$current_id) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

// ✅ Get unseen messages from each sender, including latest message_id
$sql = "SELECT 
          m.sender_id, 
          u.username, 
          COUNT(*) AS count,
          MAX(m.message_id) AS latest_message_id
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.receiver_id = ? AND m.seen_status = 0
        GROUP BY m.sender_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_id);
$stmt->execute();
$result = $stmt->get_result();

$unseenMessages = [];
while ($row = $result->fetch_assoc()) {
  $unseenMessages[] = [
    'sender_id' => $row['sender_id'],
    'username' => $row['username'],
    'count' => $row['count'],
    'latest_message_id' => $row['latest_message_id'] // ✅ added
  ];
}

echo json_encode([
  'status' => 'success',
  'unseen' => $unseenMessages
]);

$stmt->close();
$conn->close();
?>
