<?php
require_once 'sessions.php';
require_once 'db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || !isset($_FILES['profile_pic'])) {
  echo json_encode(["status" => "error", "message" => "Invalid request"]);
  exit;
}

$targetDir = "../uploads/profile_pics/";
if (!is_dir($targetDir)) {
  mkdir($targetDir, 0777, true);
}

$ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
$newName = "user_" . $user_id . "_" . time() . "." . $ext;
$targetFile = $targetDir . $newName;

if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
  $relativePath = "uploads/profile_pics/" . $newName;
  $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
  $stmt->bind_param("si", $relativePath, $user_id);
  $stmt->execute();

  
  $_SESSION['profile_pic'] = $relativePath;

  echo json_encode(["status" => "success", "newPath" => $relativePath]);
} else {
  echo json_encode(["status" => "error", "message" => "Failed to move file"]);
}
