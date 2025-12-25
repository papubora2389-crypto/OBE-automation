<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$db = getDB();
$user_id = $_SESSION['user_id'];

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-chart-pie"></i> My Progress</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title">Courses Enrolled</h6>
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                ?>
                <h3><?php echo $row['cnt']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title">Assessments Completed</h6>
                <?php
                $stmt = $db->prepare("SELECT COUNT(DISTINCT sm.assessment_id) as cnt
                                    FROM student_marks sm
                                    JOIN enrollments e ON sm.enrollment_id = e.id
                                    WHERE e.student_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                ?>
                <h3><?php echo $row['cnt']; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Course-wise Performance</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT c.id, c.code, c.name FROM enrollments e
                                   JOIN courses c ON e.course_id = c.id
                                   WHERE e.student_id = ?
                                   ORDER BY c.code");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $courses = $stmt->get_result();

                while ($course = $courses->fetch_assoc()):
                ?>
                <div class="mb-4">
                    <h6 class="text-primary"><?php echo $course['code'] . ' - ' . $course['name']; ?></h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>CO Code</th>
                                            <th>Description</th>
                                            <th>Attainment</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt2 = $db->prepare("SELECT co.id, co.code, co.description FROM course_outcomes co WHERE co.course_id = ? ORDER BY co.code");
                                        $stmt2->bind_param("i", $course['id']);
                                        $stmt2->execute();
                                        $outcomes = $stmt2->get_result();

                                        while ($co = $outcomes->fetch_assoc()):
                                            $attainment = calculateAttainment($co['id'], $course['id']);
                                            $is_attained = $attainment >= 60;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $co['code']; ?></strong></td>
                                            <td><?php echo substr($co['description'], 0, 40); ?>...</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?php echo $is_attained ? 'success' : 'danger'; ?>"
                                                         style="width: <?php echo $attainment; ?>%">
                                                        <?php echo round($attainment, 2); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $is_attained ? 'success' : 'danger'; ?> small">
                                                    <?php echo $is_attained ? 'Attained' : 'Not Attained'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-light">
                                <div class="card-body">
                                    <h6 class="card-title">Overall Course Performance</h6>
                                    <?php
                                    // Calculate overall course performance as average of CO attainments
                                    $stmt3 = $db->prepare("SELECT co.id FROM course_outcomes co WHERE co.course_id = ? ORDER BY co.code");
                                    $stmt3->bind_param("i", $course['id']);
                                    $stmt3->execute();
                                    $co_results = $stmt3->get_result();

                                    $total_attainment = 0;
                                    $co_count = 0;
                                    while ($co_row = $co_results->fetch_assoc()) {
                                        $attainment = calculateAttainment($co_row['id'], $course['id']);
                                        $total_attainment += $attainment;
                                        $co_count++;
                                    }
                                    $perf_avg = $co_count > 0 ? round($total_attainment / $co_count, 2) : 0;
                                    ?>
                                    <div class="text-center">
                                        <h4><?php echo round($perf_avg, 2); ?>%</h4>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-<?php echo $perf_avg >= 60 ? 'success' : 'warning'; ?>"
                                                 style="width: <?php echo $perf_avg; ?>%"></div>
                                        </div>
                                        <span class="badge bg-<?php echo $perf_avg >= 60 ? 'success' : 'warning'; ?>">
                                            <?php echo $perf_avg >= 60 ? 'Good Performance' : 'Needs Improvement'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tips for Success</h5>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Track your performance regularly</li>
                    <li class="mb-2"><i class="fas fa-book"></i> Review course materials</li>
                    <li class="mb-2"><i class="fas fa-tasks"></i> Complete all assessments on time</li>
                    <li class="mb-2"><i class="fas fa-comments"></i> Ask for help from your instructor</li>
                    <li><i class="fas fa-chart-line"></i> Focus on improvement areas</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
