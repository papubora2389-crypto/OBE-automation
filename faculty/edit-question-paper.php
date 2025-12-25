<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];
$paper_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

$stmt = $db->prepare("SELECT qp.*, c.id as course_id FROM question_papers qp
                     JOIN courses c ON qp.course_id = c.id
                     WHERE qp.id = ? AND qp.created_by = ?");
$stmt->bind_param("ii", $paper_id, $user_id);
$stmt->execute();
$paper = $stmt->get_result()->fetch_assoc();

if (!$paper) {
    header("Location: question-papers.php");
    exit;
}

if ($_POST) {
        if ($_POST['action'] === 'add_question') {
            $co_id = intval($_POST['co_id']);
            $question_text = sanitize($_POST['question_text']);
            $question_type = sanitize($_POST['question_type']);
            $marks = intval($_POST['marks']);
            $bloom_level = intval($_POST['bloom_level']);
            $display_order = intval($_POST['display_order']);

            // Handle diagram upload
            $diagram_path = null;
            if (isset($_FILES['diagram']) && $_FILES['diagram']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/diagrams/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_name = uniqid() . '_' . basename($_FILES['diagram']['name']);
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['diagram']['tmp_name'], $file_path)) {
                    $diagram_path = 'uploads/diagrams/' . $file_name;
                }
            }

            // Calculate current total marks
            $stmt = $db->prepare("SELECT SUM(marks) as total_marks FROM question_paper_questions WHERE paper_id = ?");
            $stmt->bind_param("i", $paper_id);
            $stmt->execute();
            $current_total = $stmt->get_result()->fetch_assoc()['total_marks'] ?? 0;

            // Check if adding this question would exceed the paper's total marks
            if ($current_total + $marks > $paper['total_marks']) {
                $msg = alert('Cannot add question. Total marks would exceed the paper\'s total marks (' . $paper['total_marks'] . '). Current total: ' . $current_total . ', Adding: ' . $marks, 'danger');
            } else {
                $stmt = $db->prepare("INSERT INTO question_paper_questions (paper_id, co_id, question_text, question_type, marks, bloom_level, display_order, diagram_path)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iississs", $paper_id, $co_id, $question_text, $question_type, $marks, $bloom_level, $display_order, $diagram_path);
                if ($stmt->execute()) {
                    $msg = alert('Question added successfully', 'success');
                }
            }
        } elseif ($_POST['action'] === 'delete_question') {
            $question_id = intval($_POST['question_id']);
            $stmt = $db->prepare("DELETE FROM question_paper_questions WHERE id = ? AND paper_id = ?");
            $stmt->bind_param("ii", $question_id, $paper_id);
            if ($stmt->execute()) {
                $msg = alert('Question deleted', 'success');
            }
        } elseif ($_POST['action'] === 'publish') {
            $stmt = $db->prepare("UPDATE question_papers SET is_published = 1 WHERE id = ? AND created_by = ?");
            $stmt->bind_param("ii", $paper_id, $user_id);
            if ($stmt->execute()) {
                $msg = alert('Question paper published', 'success');
                $paper['is_published'] = 1;
            }
        }
}

$stmt = $db->prepare("SELECT * FROM question_paper_questions WHERE paper_id = ? ORDER BY display_order");
$stmt->bind_param("i", $paper_id);
$stmt->execute();
$questions = $stmt->get_result();

$stmt = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
$stmt->bind_param("i", $paper['course_id']);
$stmt->execute();
$outcomes = $stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-file-alt"></i> Edit Question Paper</h2>
<?php echo $msg; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><?php echo $paper['title']; ?></h5>
                    <small class="text-muted">Type: <?php echo $paper['exam_type']; ?> | Total Marks: <?php echo $paper['total_marks']; ?></small>
                </div>
                <div>
                    <a href="view-question-paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-info btn-sm me-2" target="_blank">
                        <i class="fas fa-eye"></i> View Paper
                    </a>
                    <?php if (!$paper['is_published']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="publish">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Publish
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-success">Published</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                    <i class="fas fa-plus"></i> Add Question
                </button>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Q#</th>
                                <th>Question Text</th>
                                <th>Type</th>
                                <th>CO</th>
                                <th>Bloom's Level</th>
                                <th>Marks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_questions_marks = 0;
                            while ($q = $questions->fetch_assoc()):
                                $total_questions_marks += $q['marks'];
                            ?>
                            <tr>
                                <td><strong><?php echo $q['display_order']; ?></strong></td>
                                <td><?php echo substr($q['question_text'], 0, 50); ?>...</td>
                                <td><span class="badge bg-info"><?php echo $q['question_type']; ?></span></td>
                                <td>
                                    <?php
                                    if ($q['co_id']) {
                                        $co_stmt = $db->prepare("SELECT code FROM course_outcomes WHERE id = ?");
                                        $co_stmt->bind_param("i", $q['co_id']);
                                        $co_stmt->execute();
                                        $co = $co_stmt->get_result()->fetch_assoc();
                                        echo $co['code'] ?? '-';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo bloomLevelLabel(intval($q['bloom_level'])); ?></td>
                                <td><?php echo $q['marks']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <strong>Total Questions Marks:</strong> <?php echo $total_questions_marks; ?> / <?php echo $paper['total_marks']; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Paper Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <p class="form-control-plaintext"><?php echo $paper['title']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Exam Type</label>
                    <p class="form-control-plaintext"><?php echo $paper['exam_type']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Marks</label>
                    <p class="form-control-plaintext"><?php echo $paper['total_marks']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Duration (minutes)</label>
                    <p class="form-control-plaintext"><?php echo $paper['duration']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Academic Year</label>
                    <p class="form-control-plaintext"><?php echo $paper['academic_year']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester</label>
                    <p class="form-control-plaintext"><?php echo $paper['semester']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <p class="form-control-plaintext">
                        <span class="badge bg-<?php echo $paper['is_published'] ? 'success' : 'warning'; ?>">
                            <?php echo $paper['is_published'] ? 'Published' : 'Draft'; ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Bloom's Distribution</h5>
            </div>
            <div class="card-body">
                <?php
                // Reset pointer for bloom distribution calculation
                $questions->data_seek(0);
                $bloom_dist = [];
                while ($q = $questions->fetch_assoc()) {
                    $bloom = $q['bloom_level'];
                    $bloom_dist[$bloom] = ($bloom_dist[$bloom] ?? 0) + 1;
                }
                foreach ($bloom_dist as $level => $count):
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?php echo $level; ?></span>
                    <span class="badge bg-primary"><?php echo $count; ?></span>
                </div>
                <?php endforeach; ?>
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
            <form method="POST" enctype="multipart/form-data">
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
                            $outcomes->data_seek(0);
                            while ($co = $outcomes->fetch_assoc()):
                            ?>
                            <option value="<?php echo $co['id']; ?>" data-bloom="<?php echo $co['bloom_level']; ?>"><?php echo $co['code'] . ' - ' . substr($co['description'], 0, 30); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Type</label>
                        <select class="form-select" name="question_type" required>
                            <option value="MCQ">Multiple Choice (MCQ)</option>
                            <option value="Short Answer">Short Answer</option>
                            <option value="Long Answer">Long Answer</option>
                            <option value="Fill in Blank">Fill in the Blank</option>
                            <option value="True/False">True/False</option>
                            <option value="Essay">Essay</option>
                            <option value="Numerical">Numerical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bloom's Level</label>
                        <select class="form-select" name="bloom_level" id="bloomLevelSelect" required>
                            <?php foreach (getBloomLevels() as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="question_text" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diagram (Optional)</label>
                        <input type="file" class="form-control" name="diagram" accept="image/*">
                        <small class="text-muted">Upload a diagram image for this question</small>
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

<script>
document.getElementById('coSelect').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var bloomLevel = selectedOption.getAttribute('data-bloom');
    if (bloomLevel) {
        document.getElementById('bloomLevelSelect').value = bloomLevel;
    }
});
</script>
<?php include '../includes/footer.php'; ?>
