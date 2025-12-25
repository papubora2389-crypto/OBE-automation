<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$db = getDB();
$userObj = getCurrentUser();
$user_id = $userObj['user_id'] ?? null;

if (!$user_id) {
    echo "User not logged in";
    exit;
}

$stmt = $db->prepare("SELECT id, username, full_name, email, role, department, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo "<pre>";
print_r($user);
echo "</pre>";
?>
