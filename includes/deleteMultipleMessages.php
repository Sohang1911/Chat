<?php
require_once "db.php";
require_once "sessions.php";
header('Content-Type: application/json');

// ✅ Make sure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// ✅ Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Validate session and input
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$ids = $data['message_ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    echo json_encode(['status' => 'error', 'message' => 'No messages selected']);
    exit;
}

// ✅ Sanitize input
$ids = array_filter($ids, fn($id) => is_numeric($id));
if (count($ids) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid message IDs']);
    exit;
}

// ✅ Build and execute SQL
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare("DELETE FROM messages WHERE message_id IN ($placeholders)");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$ids);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Execution failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
