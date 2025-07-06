<?php
require_once "db.php";
require_once "sessions.php";

$data = json_decode(file_get_contents("php://input"), true);
$senderId = $_SESSION['user_id'];
$receiverId = intval($data['receiver_id'] ?? 0);

if ($receiverId && $senderId !== $receiverId) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, '[Chat Started]', NOW())");
    $stmt->bind_param("ii", $senderId, $receiverId);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "invalid"]);
}
?>
