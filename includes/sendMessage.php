<?php
require_once 'db.php';
require_once 'sessions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if (!$receiver_id && $message === '' && empty($_FILES['attachment'])) {
    echo json_encode(['status' => 'error', 'message' => 'Message or file required']);
    exit;
}

// Check block status
$check = $conn->prepare("SELECT 1 FROM block_list WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)");
$check->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'User is blocked or has blocked you']);
    exit;
}
$check->close();

// Handle file upload
$file_path = null;
$file_type = null;

if (!empty($_FILES['attachment']['name'])) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = time() . '_' . basename($_FILES['attachment']['name']);
    $targetFile = $uploadDir . $fileName;

    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt','webp','sql','mp3','mp4','js'];

    if (!in_array($fileType, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Unsupported file type']);
        exit;
    }

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
        $file_path = 'uploads/' . $fileName; // relative path
        $file_type = $fileType;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
        exit;
    }
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_path, file_type, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iisss", $sender_id, $receiver_id, $message, $file_path, $file_type);

try {
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Message sent']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
