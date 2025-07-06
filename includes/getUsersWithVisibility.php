<?php
require_once 'db.php';
require_once 'sessions.php';

$current_id = $_SESSION['user_id'] ?? null;
if (!$current_id) {
  echo json_encode(['status' => 'error']);
  exit;
}

// Get all users except self
$sql = "SELECT u.user_id, u.username,
        EXISTS (
          SELECT 1 FROM profile_visibility 
          WHERE user_id = ? AND allowed_user_id = u.user_id
        ) AS is_allowed
        FROM users u
        WHERE u.user_id != ?
        ORDER BY u.username";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $current_id, $current_id);
$stmt->execute();
$result = $stmt->get_result();

$users = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(['status' => 'success', 'users' => $users]);
