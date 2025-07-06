<?// searchUsers.php
require_once 'db.php';

$term = $_GET['term'] ?? '';
$term = "%$term%";

$stmt = $conn->prepare("SELECT user_id, username FROM users WHERE username LIKE ?");
$stmt->bind_param("s", $term);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
