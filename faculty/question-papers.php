<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];
$msg = '';

if ($_POST) {
    if ($_POST['action'] === 'create') {
        $course_id = intval($_POST['course_id']);
        $title = sanitize($_POST['title']);
        $exam_type = sanitize($_POST['exam_type']);
        $total_marks = intval($_POST['total_marks']);
        $duration = intval($_POST['duration']);
        $academic_year = sanitize($_POST['academic_year']);
        $semester = sanitize($_POST['semester']);

        $stmt = $db->prepare("INSERT INTO question_papers (course_id, title, exam_type, total_marks, duration, academic_year, semester, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiissi", $course_id, $title, $exam_type, $total_marks, $duration, $academic_year, $semester, $user_id);
        if ($stmt->execute()) {
            $paper_id = $db->lastInsertId();

            // Automatically create corresponding assessment
            $weightage = 0;
            switch ($exam_type) {
                case 'Sessional 1':
                case 'Sessional 2':
                    $weightage = 10;
                    break;
                case 'Assignment':
                    $weightage = 5;
                    break;
                case 'Mid Semester Examination':
                    $weightage = 30;
                    break;
                case 'End Semester Examination':
                    $weightage = 50;
                    break;
                case 'Lab Examination':
                    $weightage = 50;
                    break;
                default:
                    $weightage = 10;
            }

            $assessment_stmt = $db->prepare("INSERT INTO assessments (course_id, name, type, max_marks, weightage, assessment_date) VALUES (?, ?, ?, ?, ?, CURDATE())");
            $assessment_stmt->bind_param("issdd", $course_id, $title, $exam_type, $total_marks, $weightage);
            $assessment_stmt->execute();
            $assessment_id = $db->lastInsertId();

            $msg = alert("Question paper created! <a href='edit-question-paper.php?id=$paper_id'>Edit now</a> | <a href='assessments.php'>View Assessment</a>", 'success');
        }
    } elseif ($_POST['action'] === 'delete') {
        $paper_id = intval($_POST['paper_id']);

        // Check if the paper belongs to the current faculty member and get details
        $stmt = $db->prepare("SELECT title, exam_type, course_id FROM question_papers WHERE id = ? AND created_by = ?");
        $stmt->bind_param("ii", $paper_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $paper = $result->fetch_assoc();

            // Delete associated questions first
            $stmt = $db->prepare("DELETE FROM question_paper_questions WHERE paper_id = ?");
            $stmt->bind_param("i", $paper_id);
            $stmt->execute();

            // Delete related assessment
            $stmt = $db->prepare("DELETE FROM assessments WHERE name = ? AND type = ? AND course_id = ?");
            $stmt->bind_param("ssi", $paper['title'], $paper['exam_type'], $paper['course_id']);
            $stmt->execute();

            // Delete the question paper
            $stmt = $db->prepare("DELETE FROM question_papers WHERE id = ? AND created_by = ?");
            $stmt->bind_param("ii", $paper_id, $user_id);
            if ($stmt->execute()) {
                $msg = alert('Question paper deleted successfully', 'success');
            } else {
                $msg = alert('Failed to delete question paper', 'danger');
            }
        } else {
            $msg = alert('Question paper not found or access denied', 'danger');
        }
    }
}

$stmt = $db->prepare("SELECT qp.*, c.code, c.name FROM question_papers qp
                     JOIN courses c ON qp.course_id = c.id
                     WHERE qp.created_by = ? ORDER BY qp.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-file-alt"></i> Question Papers</h2>
<?php echo $msg; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">
    <i class="fas fa-plus"></i> Create Question Paper
</button>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Marks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['code']; ?></td>
                        <td><?php echo $row['exam_type']; ?></td>
                        <td><?php echo $row['total_marks']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $row['is_published'] ? 'success' : 'warning'; ?>">
                                <?php echo $row['is_published'] ? 'Published' : 'Draft'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit-question-paper.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="view-question-paper.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question paper?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="paper_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Question Paper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select class="form-select" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY code");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $course_result = $stmt->get_result();
                            while ($course = $course_result->fetch_assoc()):
                            ?>
                            <option value="<?php echo $course['id']; ?>" data-name="<?php echo $course['name']; ?>"><?php echo $course['code']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="titleInput" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Exam Type</label>
                                <select class="form-select" name="exam_type" required>
                                    <?php foreach (getAssessmentTypes() as $t): ?>
                                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total Marks</label>
                                <input type="number" class="form-control" name="total_marks" value="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Duration (min)</label>
                                <input type="number" class="form-control" name="duration" value="180" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-control" name="academic_year" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="Spring">Spring</option>
                                    <option value="Autumn">Autumn</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function updateModalTitle() {
    var select = document.querySelector('select[name="course_id"]');
    var selectedOption = select.options[select.selectedIndex];
    var courseName = selectedOption.getAttribute('data-name');
    if (courseName) {
        document.getElementById('modalTitle').textContent = courseName;
        document.getElementById('titleInput').value = courseName;
    } else {
        document.getElementById('modalTitle').textContent = 'Create Question Paper';
        document.getElementById('titleInput').value = '';
    }
}

function updateExamDetails() {
    var examType = document.querySelector('select[name="exam_type"]').value;
    var marksInput = document.querySelector('input[name="total_marks"]');
    var durationInput = document.querySelector('input[name="duration"]');

    if (examType === 'Sessional 1' || examType === 'Sessional 2' || examType === 'Assignment') {
        marksInput.value = 10;
        durationInput.value = 30;
    } else if (examType === 'Mid Semester Examination') {
        marksInput.value = 30;
        durationInput.value = 90;
    } else if (examType === 'End Semester Examination') {
        marksInput.value = 50;
        durationInput.value = 180;
    } else if (examType === 'Lab Examination') {
        marksInput.value = 50;
        // Duration not specified, leave as is
    }
}

document.querySelector('select[name="course_id"]').addEventListener('change', updateModalTitle);
document.querySelector('select[name="exam_type"]').addEventListener('change', updateExamDetails);

// Initialize exam details on page load
updateExamDetails();
</script>
<?php include '../includes/footer.php'; ?>
