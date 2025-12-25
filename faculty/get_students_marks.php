<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];

$assessment_id = intval($_GET['assessment_id']);
$course_id = intval($_GET['course_id']);
$max_marks = intval($_GET['max_marks']);

// Verify the assessment belongs to the faculty
$stmt = $db->prepare("SELECT a.id FROM assessments a
                     JOIN courses c ON a.course_id = c.id
                     WHERE a.id = ? AND c.faculty_id = ?");
$stmt->bind_param("ii", $assessment_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows == 0) {
    echo '<tr><td colspan="4" class="text-center text-danger">Access denied</td></tr>';
    exit;
}

// Get enrolled students with roll numbers
$stmt = $db->prepare("SELECT DISTINCT e.id as enrollment_id, u.id as student_id, u.username as roll_no, u.full_name FROM enrollments e
                     JOIN users u ON e.student_id = u.id
                     WHERE e.course_id = ? ORDER BY u.full_name");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$students = $stmt->get_result();

while ($student = $students->fetch_assoc()):
    // Get existing marks
    $stmt = $db->prepare("SELECT sm.marks_obtained FROM student_marks sm
                        WHERE sm.enrollment_id = ? AND sm.assessment_id = ?");
    $stmt->bind_param("ii", $student['enrollment_id'], $assessment_id);
    $stmt->execute();
    $marks_rec = $stmt->get_result()->fetch_assoc();

    $marks_obtained = $marks_rec ? $marks_rec['marks_obtained'] : null;
    $percentage = $marks_obtained !== null ? ($marks_obtained / $max_marks) * 100 : 0;
?>
<tr>
    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
    <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
    <td>
        <?php if ($marks_obtained !== null): ?>
            <span><?php echo $marks_obtained; ?></span>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>
    <td>
        <button class="btn btn-sm btn-primary"
                onclick="openRecordMarksModal(<?php echo $student['student_id']; ?>, '<?php echo addslashes($student['full_name']); ?>', '<?php echo $marks_obtained ?? ''; ?>', <?php echo $max_marks; ?>)">
            <i class="fas fa-edit"></i>
        </button>
    </td>
</tr>
<?php endwhile; ?>
