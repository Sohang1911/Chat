<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$receiver = $_SESSION['user_id'] ?? null;
$sender = intval($data['sender_id'] ?? 0);

if (!$receiver || !$sender) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user']);
    exit;
}

$stmt = $conn->prepare("UPDATE messages SET seen_status = 1 WHERE sender_id = ? AND receiver_id = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Query preparation failed']);
    exit;
}

$stmt->bind_param("ii", $sender, $receiver);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark messages as seen']);
}

$stmt->close();
$conn->close();
