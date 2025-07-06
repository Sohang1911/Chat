<?php
require_once 'db.php';
require_once 'sessions.php';
header('Content-Type: application/json');

$sender_id = $_SESSION['user_id'] ?? null;
$receiver_id = $_GET['receiver_id'] ?? null;

if (!$sender_id || !$receiver_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user information']);
    exit();
}

// Check if blocked
$check = $conn->prepare("SELECT 1 FROM block_list WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)");
$check->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Messages blocked']);
    exit;
}
$check->close();

$stmt = $conn->prepare("
    SELECT m.message_id, m.message, m.timestamp, m.seen_status,
           m.file_path, m.file_type,                        -- ✅ Added file fields
           u1.username AS sender, u2.username AS receiver
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.user_id
    JOIN users u2 ON m.receiver_id = u2.user_id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.timestamp ASC
");
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $timestamp = strtotime($row['timestamp']);
    $msgTime = date("h:i A", $timestamp);

    $msgDate = date("Y-m-d", $timestamp);
    $today = date("Y-m-d");
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $displayDate = $msgDate === $today ? "Today" : ($msgDate === $yesterday ? "Yesterday" : date("d-m-Y", $timestamp));

    $messages[] = [
        'id' => (int)$row['message_id'],
        'sender' => $row['sender'],
        'receiver' => $row['receiver'],
        'message' => $row['message'],
        'time' => $msgTime,
        'date' => $displayDate,
        'seen_status' => (int)$row['seen_status'],
        'file_path' => $row['file_path'] ?? null,           // ✅ Added
        'file_type' => $row['file_type'] ?? null            // ✅ Added
    ];
}

echo json_encode(['status' => 'success', 'messages' => $messages]);

$stmt->close();
$conn->close();
?>
