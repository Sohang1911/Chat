<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.user_id, u.username 
    FROM block_list b
    JOIN users u ON u.user_id = b.blocked_id
    WHERE b.blocker_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['status' => 'success', 'users' => $users]);
?>
