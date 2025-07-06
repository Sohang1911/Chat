<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
    u.user_id, 
    u.username,
    u.status,
    CASE 
      WHEN pv.allowed_user_id IS NOT NULL OR u.user_id = ? 
      THEN u.profile_pic 
      ELSE 'assets/images/default-avatar.webp' 
    END AS profile_pic,
    (
        SELECT COUNT(*) 
        FROM messages 
        WHERE sender_id = u.user_id 
          AND receiver_id = ? 
          AND seen_status = 0
    ) AS unread_count
FROM users u
LEFT JOIN profile_visibility pv 
  ON pv.user_id = u.user_id AND pv.allowed_user_id = ?
WHERE u.user_id != ?
AND u.user_id NOT IN (
    SELECT blocked_id FROM block_list WHERE blocker_id = ?
)
AND u.user_id NOT IN (
    SELECT blocker_id FROM block_list WHERE blocked_id = ?
)
AND (
    u.user_id IN (
        SELECT DISTINCT sender_id FROM messages WHERE receiver_id = ?
    ) OR
    u.user_id IN (
        SELECT DISTINCT receiver_id FROM messages WHERE sender_id = ?
    )
)
ORDER BY 
  (
    SELECT MAX(timestamp) FROM messages 
    WHERE 
      (sender_id = u.user_id AND receiver_id = ?) OR 
      (sender_id = ? AND receiver_id = u.user_id)
  ) DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'SQL error: ' . $conn->error]);
    exit;
}

// 10 parameters
$stmt->bind_param(
    "iiiiiiiiii", 
    $user_id,  // for: u.user_id = ?
    $user_id,  // for: receiver_id
    $user_id,  // for: pv.allowed_user_id
    $user_id,  // for: u.user_id != ?
    $user_id,  // blocker_id
    $user_id,  // blocked_id
    $user_id,  // receiver_id
    $user_id,  // sender_id
    $user_id,  // latest message condition
    $user_id   // latest message condition
);

$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status' => 'success', 'users' => $users]);
?>
