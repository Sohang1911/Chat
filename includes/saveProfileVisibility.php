<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');
$current_id = $_SESSION['user_id'] ?? null;

if (!$current_id) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$allowed_ids = $data['allowed_ids'] ?? [];

$conn->begin_transaction();
$conn->query("DELETE FROM profile_visibility WHERE user_id = $current_id");

$stmt = $conn->prepare("INSERT INTO profile_visibility (user_id, allowed_user_id) VALUES (?, ?)");
foreach ($allowed_ids as $allowed) {
  $stmt->bind_param("ii", $current_id, $allowed);
  $stmt->execute();
}
$conn->commit();

echo json_encode(['status' => 'success']);
