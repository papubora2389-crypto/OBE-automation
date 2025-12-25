<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid file ID');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'download';
if (!in_array($action, ['view', 'download'])) {
    die('Invalid action');
}

$file_id = intval($_GET['id']);
$db = getDB();
$user = getCurrentUser();
$user_id = $user['user_id'];
$user_role = $user['role'];

// Get file details
$stmt = $db->prepare("SELECT cf.*, c.id as course_id FROM course_files cf
                     JOIN courses c ON cf.course_id = c.id
                     WHERE cf.id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    die('File not found');
}

// Check permissions based on role
if ($user_role === 'student') {
    // Check if student is enrolled in the course
    $stmt = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $user_id, $file['course_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        die('Access denied');
    }
} elseif ($user_role === 'faculty') {
    // Faculty can only access files they uploaded or files from their courses
    if ($file['uploaded_by'] !== $user_id) {
        $stmt = $db->prepare("SELECT id FROM courses WHERE id = ? AND faculty_id = ?");
        $stmt->bind_param("ii", $file['course_id'], $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            die('Access denied');
        }
    }
} elseif ($user_role !== 'admin') {
    die('Access denied');
}

// Check if file exists on server
if (!file_exists($file['file_path'])) {
    die('File not found on server');
}

// Determine content type based on file extension
$extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'txt' => 'text/plain',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif'
];
$content_type = $content_types[$extension] ?? 'application/octet-stream';

// Set headers based on action
if ($action === 'view') {
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: inline; filename="' . $file['file_name'] . '"');
} else {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
}
header('Content-Length: ' . filesize($file['file_path']));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($file['file_path']);
exit;
?>
