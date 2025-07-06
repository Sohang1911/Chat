<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$messageId = intval($data['id']);
$newMessage = trim($data['message']);
$userId = $_SESSION['user_id'];

if (!$messageId || $newMessage === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// Ensure the logged-in user owns the message
$stmt = $conn->prepare("UPDATE messages SET message = ? WHERE message_id = ? AND sender_id = ?");
$stmt->bind_param("sii", $newMessage, $messageId, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}
$stmt->close();
$conn->close();
