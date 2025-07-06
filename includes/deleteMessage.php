<?php
require_once 'db.php';
require_once 'sessions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$messageId = intval($data['message_id'] ?? 0);
$userId = $_SESSION['user_id'] ?? 0;

if (!$messageId || !$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ? AND sender_id = ?");
$stmt->bind_param("ii", $messageId, $userId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized or not found']);
}
