<?php
require_once "db.php";
require_once "sessions.php";

header('Content-Type: application/json');

$currentId = $_SESSION['user_id'];

$search = isset($_POST['term']) ? trim($_POST['term']) : '';
$response = [];

if ($search !== '') {
    $stmt = $conn->prepare("
    SELECT 
        u.user_id, 
        u.username,
        u.status,
        CASE 
            WHEN pv.allowed_user_id IS NOT NULL OR u.user_id = ? 
            THEN u.profile_pic 
            ELSE 'assets/images/default-avatar.webp' 
        END AS profile_pic
    FROM users u
    LEFT JOIN profile_visibility pv ON pv.user_id = u.user_id AND pv.allowed_user_id = ?
    WHERE u.username LIKE CONCAT('%', ?, '%')
      AND u.user_id != ?
      AND u.user_id NOT IN (
          SELECT blocked_id FROM block_list WHERE blocker_id = ?
          UNION
          SELECT blocker_id FROM block_list WHERE blocked_id = ?
      )
");
$stmt->bind_param("iisiis", $currentId, $currentId, $search, $currentId, $currentId, $currentId);

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    $stmt->close();
}

echo json_encode(["users" => $response]);
?>
