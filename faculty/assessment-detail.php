<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

$stmt = $db->prepare("SELECT a.*, c.code, c.name, c.id as course_id FROM assessments a
                     JOIN courses c ON a.course_id = c.id
                     WHERE a.id = ? AND c.faculty_id = ?");
$stmt->bind_param("ii", $assessment_id, $user_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();

if (!$assessment) {
    header("Location: assessments.php");
    exit;
}

if ($_POST) {
    if (isset($_POST['student_id']) && isset($_POST['marks'])) {
        $student_id = intval($_POST['student_id']);
        $marks = floatval($_POST['marks']);

        $stmt = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $student_id, $assessment['course_id']);
        $stmt->execute();
        $enrollment = $stmt->get_result()->fetch_assoc();

        if ($enrollment) {
            $stmt = $db->prepare("INSERT INTO student_marks (enrollment_id, assessment_id, marks_obtained)
                                 VALUES (?, ?, ?)
                                 ON DUPLICATE KEY UPDATE marks_obtained = ?");
            $stmt->bind_param("iiii", $enrollment['id'], $assessment_id, $marks, $marks);
            if ($stmt->execute()) {
                $msg = alert('Marks recorded successfully', 'success');
            }
        }
    } elseif ($_POST['action'] === 'add_question') {
        $co_id = intval($_POST['co_id']) ?: null;
        $question_text = sanitize($_POST['question_text']);
        $marks = intval($_POST['marks']);
        $bloom_level = intval($_POST['bloom_level']);
        $display_order = intval($_POST['display_order']);

        $stmt = $db->prepare("INSERT INTO assessment_questions (assessment_id, co_id, question_text, marks, bloom_level, display_order)
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiii", $assessment_id, $co_id, $question_text, $marks, $bloom_level, $display_order);
        if ($stmt->execute()) {
            $msg = alert('Question added successfully', 'success');
        } else {
            $msg = alert('Error adding question: ' . $stmt->error, 'danger');
        }
    }
}

$stmt = $db->prepare("SELECT DISTINCT u.id, u.username, u.full_name FROM enrollments e
                     JOIN users u ON e.student_id = u.id
                     WHERE e.course_id = ? ORDER BY u.full_name");
$stmt->bind_param("i", $assessment['course_id']);
$stmt->execute();
$students = $stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4">Assessment: <?php echo $assessment['name']; ?></h2>
<?php echo $msg; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assessment Details</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                    <i class="fas fa-plus"></i> Add Question
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Course:</strong> <?php echo $assessment['code'] . ' - ' . $assessment['name']; ?></p>
                        <p><strong>Type:</strong> <?php echo $assessment['type']; ?></p>
                        <p><strong>Max Marks:</strong> <?php echo $assessment['max_marks']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Weightage:</strong> <?php echo $assessment['weightage']; ?>%</p>
                        <p><strong>Date:</strong> <?php echo formatDate($assessment['assessment_date']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Assessment Questions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Question</th>
                                <th>Marks</th>
                                <th>Bloom's Level</th>
                                <th>Course Outcome</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->prepare("SELECT aq.*, co.code as co_code FROM assessment_questions aq
                                                LEFT JOIN course_outcomes co ON aq.co_id = co.id
                                                WHERE aq.assessment_id = ? ORDER BY aq.display_order");
                            $stmt->bind_param("i", $assessment_id);
                            $stmt->execute();
                            $questions = $stmt->get_result();
                            while ($q = $questions->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $q['display_order']; ?></td>
                                <td><?php echo htmlspecialchars(substr($q['question_text'], 0, 100)); ?><?php echo strlen($q['question_text']) > 100 ? '...' : ''; ?></td>
                                <td><?php echo $q['marks']; ?></td>
                                <td><?php echo bloomLevelLabel($q['bloom_level']); ?></td>
                                <td><?php echo $q['co_code'] ?: '-'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Record Marks</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Roll Number</th>
                                <th>Marks Obtained</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $students->data_seek(0);
                            while ($student = $students->fetch_assoc()):
                                $stmt = $db->prepare("SELECT sm.marks_obtained FROM student_marks sm
                                                    JOIN enrollments e ON sm.enrollment_id = e.id
                                                    WHERE e.student_id = ? AND sm.assessment_id = ?");
                                $stmt->bind_param("ii", $student['id'], $assessment_id);
                                $stmt->execute();
                                $marks_rec = $stmt->get_result()->fetch_assoc();
                                $percentage = $marks_rec ? ($marks_rec['marks_obtained'] / $assessment['max_marks']) * 100 : 0;
                            ?>
                            <tr>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['username']; ?></td>
                                <td>
                                    <?php if ($marks_rec): ?>
                                        <span><?php echo $marks_rec['marks_obtained']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($marks_rec): ?>
                                        <span class="badge bg-<?php echo $percentage >= 60 ? 'success' : 'danger'; ?>">
                                            <?php echo round($percentage, 2); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo ($marks_rec && $percentage >= 60) ? '<span class="badge bg-success">Attained</span>' : '<span class="badge bg-danger">Not Attained</span>'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#marksModal"
                                            data-student-id="<?php echo $student['id']; ?>"
                                            data-student-name="<?php echo $student['full_name']; ?>"
                                            data-marks="<?php echo $marks_rec['marks_obtained'] ?? ''; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT COUNT(DISTINCT e.student_id) as total, COUNT(sm.id) as submitted
                                    FROM enrollments e
                                    LEFT JOIN student_marks sm ON e.id = sm.enrollment_id AND sm.assessment_id = ?
                                    WHERE e.course_id = ?");
                $stmt->bind_param("ii", $assessment_id, $assessment['course_id']);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();

                $stmt = $db->prepare("SELECT AVG(marks_obtained) as avg_marks FROM student_marks WHERE assessment_id = ?");
                $stmt->bind_param("i", $assessment_id);
                $stmt->execute();
                $avg = $stmt->get_result()->fetch_assoc();
                ?>
                <div class="mb-3">
                    <p><strong>Total Students:</strong> <?php echo $stats['total']; ?></p>
                    <p><strong>Submitted:</strong> <?php echo $stats['submitted']; ?></p>
                    <p><strong>Pending:</strong> <?php echo $stats['total'] - $stats['submitted']; ?></p>
                </div>
                <hr>
                <p><strong>Average Marks:</strong> <?php echo round($avg['avg_marks'] ?? 0, 2); ?>/<?php echo $assessment['max_marks']; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addQuestionModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_question">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Marks</label>
                                <input type="number" class="form-control" name="marks" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Outcome (Optional)</label>
                        <select class="form-select" name="co_id" id="coSelect">
                            <option value="">Select CO</option>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
                            $stmt->bind_param("i", $assessment['course_id']);
                            $stmt->execute();
                            $outcomes = $stmt->get_result();
                            while ($co = $outcomes->fetch_assoc()):
                            ?>
                            <option value="<?php echo $co['id']; ?>" data-bloom="<?php echo $co['bloom_level']; ?>"><?php echo $co['code'] . ' - ' . substr($co['description'], 0, 30); ?>...</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bloom's Level</label>
                        <input type="text" class="form-control" id="bloomLevelDisplay" readonly required>
                        <input type="hidden" name="bloom_level" id="bloomLevelInput" value="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="question_text" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="marksModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Marks - <span id="studentName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="studentId">
                    <div class="mb-3">
                        <label class="form-label">Marks Out of <?php echo $assessment['max_marks']; ?></label>
                        <input type="number" step="0.5" min="0" max="<?php echo $assessment['max_marks']; ?>"
                               class="form-control" name="marks" id="marks" required>
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
    document.getElementById('studentId').value = btn.dataset.studentId;
    document.getElementById('studentName').textContent = btn.dataset.studentName;
    document.getElementById('marks').value = btn.dataset.marks || '';
});

document.getElementById('coSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const bloomLevelNum = selectedOption.getAttribute('data-bloom') || '1';
    const bloomLevels = {
        1: 'Remembering',
        2: 'Understanding',
        3: 'Applying',
        4: 'Analyzing',
        5: 'Evaluating',
        6: 'Creating'
    };
    const bloomLabel = bloomLevels[bloomLevelNum] || 'Remembering';
    document.getElementById('bloomLevelDisplay').value = bloomLabel;
    document.getElementById('bloomLevelInput').value = bloomLevelNum;
});
</script>

<?php include '../includes/footer.php'; ?>
