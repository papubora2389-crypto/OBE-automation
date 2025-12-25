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
    if ($_POST['action'] === 'add') {
        $course_id = intval($_POST['course_id']);
        $name = sanitize($_POST['name']);
        $type = sanitize($_POST['type']);
        $max_marks = intval($_POST['max_marks']);
        $weightage = floatval($_POST['weightage']);
        $date = $_POST['assessment_date'];

        $stmt = $db->prepare("INSERT INTO assessments (course_id, name, type, max_marks, weightage, assessment_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssds", $course_id, $name, $type, $max_marks, $weightage, $date);
        if ($stmt->execute()) {
            $msg = alert('Assessment added successfully', 'success');
        }
    } elseif ($_POST['action'] === 'delete') {
        $assessment_id = intval($_POST['assessment_id']);

        // Check if the assessment belongs to the current faculty member
        $stmt = $db->prepare("SELECT a.id FROM assessments a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND c.faculty_id = ?");
        $stmt->bind_param("ii", $assessment_id, $user_id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            // Get assessment details
            $stmt = $db->prepare("SELECT name, type, course_id FROM assessments WHERE id = ?");
            $stmt->bind_param("i", $assessment_id);
            $stmt->execute();
            $assessment = $stmt->get_result()->fetch_assoc();

            // Delete related question paper
            $stmt = $db->prepare("DELETE FROM question_papers WHERE title = ? AND exam_type = ? AND course_id = ? AND created_by = ?");
            $stmt->bind_param("ssii", $assessment['name'], $assessment['type'], $assessment['course_id'], $user_id);
            $stmt->execute();

            // Delete the assessment
            $stmt = $db->prepare("DELETE FROM assessments WHERE id = ?");
            $stmt->bind_param("i", $assessment_id);
            if ($stmt->execute()) {
                $msg = alert('Assessment deleted successfully', 'success');
            } else {
                $msg = alert('Failed to delete assessment', 'danger');
            }
        } else {
            $msg = alert('Assessment not found or access denied', 'danger');
        }
    }
}

$stmt = $db->prepare("SELECT a.*, c.code, c.name FROM assessments a
                     JOIN courses c ON a.course_id = c.id
                     WHERE c.faculty_id = ? ORDER BY a.assessment_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-clipboard-check"></i> Assessments</h2>
<?php echo $msg; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Max Marks</th>

                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['code']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo $row['max_marks']; ?></td>
                        <td><?php echo $row['weightage']; ?>%</td>
                        <td><?php echo formatDate($row['assessment_date']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#marksModal"
                                    data-assessment-id="<?php echo $row['id']; ?>"
                                    data-assessment-name="<?php echo $row['name']; ?>"
                                    data-max-marks="<?php echo $row['max_marks']; ?>"
                                    data-course-id="<?php echo $row['course_id']; ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this assessment?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="assessment_id" value="<?php echo $row['id']; ?>">
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

<div class="modal fade" id="marksModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Marks - <span id="assessmentTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Enrollment ID</th>
                                <th>Marks Obtained</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <!-- Students will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recordMarksModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Marks - <span id="studentName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="marksForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="record_marks">
                    <input type="hidden" name="student_id" id="studentId">
                    <input type="hidden" name="assessment_id" id="currentAssessmentId">
                    <div class="mb-3">
                        <label class="form-label">Marks Out of <span id="maxMarksDisplay"></span></label>
                        <input type="number" step="0.5" min="0" class="form-control" name="marks" id="marksInput" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('marksModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const assessmentId = btn.dataset.assessmentId;
    const assessmentName = btn.dataset.assessmentName;
    const maxMarks = btn.dataset.maxMarks;
    const courseId = btn.dataset.courseId;

    document.getElementById('assessmentTitle').textContent = assessmentName;
    document.getElementById('currentAssessmentId').value = assessmentId;

    // Load students for this assessment
    fetchStudents(assessmentId, courseId, maxMarks);
});

function fetchStudents(assessmentId, courseId, maxMarks) {
    fetch('get_students_marks.php?assessment_id=' + assessmentId + '&course_id=' + courseId + '&max_marks=' + maxMarks)
        .then(response => response.text())
        .then(data => {
            document.getElementById('studentsTableBody').innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentsTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading students</td></tr>';
        });
}

function openRecordMarksModal(studentId, studentName, currentMarks, maxMarks) {
    document.getElementById('studentId').value = studentId;
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('marksInput').value = currentMarks || '';
    document.getElementById('maxMarksDisplay').textContent = maxMarks;
    document.getElementById('marksInput').max = maxMarks;

    // Close the marks modal and open the record marks modal
    const marksModal = bootstrap.Modal.getInstance(document.getElementById('marksModal'));
    marksModal.hide();

    const recordMarksModal = new bootstrap.Modal(document.getElementById('recordMarksModal'));
    recordMarksModal.show();
}

document.getElementById('marksForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('record_marks.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Marks recorded successfully!');
            // Close the record marks modal
            const recordMarksModal = bootstrap.Modal.getInstance(document.getElementById('recordMarksModal'));
            recordMarksModal.hide();

            // Refresh the marks modal
            const assessmentId = document.getElementById('currentAssessmentId').value;
            const btn = document.querySelector('[data-assessment-id="' + assessmentId + '"]');
            if (btn) {
                const courseId = btn.dataset.courseId;
                const maxMarks = btn.dataset.maxMarks;
                fetchStudents(assessmentId, courseId, maxMarks);
                // Reopen the marks modal
                const marksModal = new bootstrap.Modal(document.getElementById('marksModal'));
                marksModal.show();
            }
        } else {
            alert('Error recording marks: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error recording marks');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
