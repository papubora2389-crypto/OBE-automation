<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$db = getDB();
$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT c.*, p.name as prog_name FROM enrollments e
                     JOIN courses c ON e.course_id = c.id
                     JOIN programs p ON c.program_id = p.id
                     WHERE c.id = ? AND e.student_id = ?");
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: courses.php");
    exit;
}

include '../includes/header.php';
?>

<h2 class="mb-4">
    <i class="fas fa-book"></i> <?php echo $course['code']; ?> - <?php echo $course['name']; ?>
</h2>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
            Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="outcomes-tab" data-bs-toggle="tab" data-bs-target="#outcomes" type="button">
            Outcomes
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="marks-tab" data-bs-toggle="tab" data-bs-target="#marks" type="button">
            My Marks
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button">
            Materials
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="overview">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Course Details</div>
                    <div class="card-body">
                        <p><strong>Program:</strong> <?php echo $course['prog_name']; ?></p>
                        <p><strong>Code:</strong> <?php echo $course['code']; ?></p>
                        <p><strong>Name:</strong> <?php echo $course['name']; ?></p>
                        <p><strong>Credits:</strong> <?php echo $course['credits']; ?></p>
                        <p><strong>Semester:</strong> <?php echo $course['semester']; ?></p>
                        <p><strong>Academic Year:</strong> <?php echo $course['academic_year']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Your Performance</div>
                    <div class="card-body">
                        <?php
                        $stmt = $db->prepare("SELECT AVG((sm.marks_obtained / a.max_marks) * 100) as avg
                                           FROM student_marks sm
                                           JOIN assessments a ON sm.assessment_id = a.id
                                           JOIN enrollments e ON sm.enrollment_id = e.id
                                           WHERE e.student_id = ? AND a.course_id = ?");
                        $stmt->bind_param("ii", $user_id, $course_id);
                        $stmt->execute();
                        $perf = $stmt->get_result()->fetch_assoc();
                        $avg_perf = $perf['avg'] ?? 0;
                        ?>
                        <h3><?php echo round($avg_perf, 2); ?>%</h3>
                        <p class="text-muted">Average Performance</p>
                        <div class="progress">
                            <div class="progress-bar bg-<?php echo $avg_perf >= 60 ? 'success' : 'danger'; ?>"
                                 style="width: <?php echo $avg_perf; ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="outcomes">
        <div class="card">
            <div class="card-header">Course Learning Outcomes</div>
            <div class="card-body">
                <div class="list-group">
                    <?php
                    $stmt = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($co = $result->fetch_assoc()):
                    ?>
                    <div class="list-group-item">
                        <h6 class="mb-1"><?php echo $co['code']; ?></h6>
                        <p class="mb-2"><?php echo $co['description']; ?></p>
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> Bloom's Level: <?php echo $co['bloom_level']; ?>
                        </small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="marks">
        <div class="card">
            <div class="card-header">Assessment Marks</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Assessment</th>
                                <th>Type</th>
                                <th>Marks Obtained</th>
                                <th>Max Marks</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->prepare("SELECT a.*, sm.marks_obtained FROM assessments a
                                               LEFT JOIN student_marks sm ON a.id = sm.assessment_id AND sm.enrollment_id = (
                                                   SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?
                                               )
                                               WHERE a.course_id = ?
                                               ORDER BY a.assessment_date");
                            $stmt->bind_param("iii", $user_id, $course_id, $course_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($assessment = $result->fetch_assoc()):
                                if ($assessment['marks_obtained']) {
                                    $percentage = ($assessment['marks_obtained'] / $assessment['max_marks']) * 100;
                                }
                            ?>
                            <tr>
                                <td><?php echo $assessment['name']; ?></td>
                                <td><?php echo $assessment['type']; ?></td>
                                <td><?php echo $assessment['marks_obtained'] ?? '-'; ?></td>
                                <td><?php echo $assessment['max_marks']; ?></td>
                                <td>
                                    <?php if ($assessment['marks_obtained']): ?>
                                        <span class="badge bg-<?php echo $percentage >= 60 ? 'success' : 'danger'; ?>">
                                            <?php echo round($percentage, 2); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="materials">
        <div class="card">
            <div class="card-header">Course Materials</div>
            <div class="card-body">
                <div class="list-group">
                    <?php
                    $stmt = $db->prepare("SELECT * FROM course_files WHERE course_id = ? ORDER BY upload_date DESC");
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 0) {
                        echo '<div class="alert alert-info">No materials available yet</div>';
                    }

                    while ($file = $result->fetch_assoc()):
                    ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-file"></i> <?php echo $file['file_name']; ?>
                            </h6>
                            <small class="text-muted">
                                <?php echo $file['file_type']; ?> | <?php echo formatDate($file['upload_date']); ?>
                            </small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-info me-2"><?php echo round($file['file_size'] / 1024, 2); ?> KB</span>
                            <a href="../download.php?id=<?php echo $file['id']; ?>&action=view" class="btn btn-sm btn-outline-primary me-1" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="../download.php?id=<?php echo $file['id']; ?>&action=download" class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
