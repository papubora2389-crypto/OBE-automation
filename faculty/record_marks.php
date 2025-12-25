<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

header('Content-Type: application/json');

$db = getDB();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$student_id = intval($_POST['student_id'] ?? 0);
$assessment_id = intval($_POST['assessment_id'] ?? 0);
$marks = floatval($_POST['marks'] ?? 0);

if ($action !== 'record_marks' || !$student_id || !$assessment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Verify the assessment belongs to the faculty
$stmt = $db->prepare("SELECT a.id, a.max_marks FROM assessments a
                     JOIN courses c ON a.course_id = c.id
                     WHERE a.id = ? AND c.faculty_id = ?");
$stmt->bind_param("ii", $assessment_id, $user_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();

if (!$assessment) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Validate marks
if ($marks < 0 || $marks > $assessment['max_marks']) {
    echo json_encode(['success' => false, 'message' => 'Marks must be between 0 and ' . $assessment['max_marks']]);
    exit;
}

// Get enrollment ID
$stmt = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = (
    SELECT course_id FROM assessments WHERE id = ?
)");
$stmt->bind_param("ii", $student_id, $assessment_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    echo json_encode(['success' => false, 'message' => 'Student not enrolled in this course']);
    exit;
}

// Check if marks already exist for this student and assessment
$stmt = $db->prepare("SELECT id FROM student_marks WHERE enrollment_id = ? AND assessment_id = ?");
$stmt->bind_param("ii", $enrollment['id'], $assessment_id);
$stmt->execute();
$existing_record = $stmt->get_result()->fetch_assoc();

if ($existing_record) {
    // Update existing record
    $stmt = $db->prepare("UPDATE student_marks SET marks_obtained = ? WHERE enrollment_id = ? AND assessment_id = ?");
    $stmt->bind_param("dii", $marks, $enrollment['id'], $assessment_id);
    $message = 'Marks updated successfully';
} else {
    // Insert new record
    $stmt = $db->prepare("INSERT INTO student_marks (enrollment_id, assessment_id, marks_obtained) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $enrollment['id'], $assessment_id, $marks);
    $message = 'Marks recorded successfully';
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record marks']);
}
?>
