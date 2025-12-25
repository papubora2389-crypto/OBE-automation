<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-chart-bar"></i> CO Attainment Analysis</h2>

<div class="row">
    <?php
    $stmt = $db->prepare("SELECT DISTINCT c.id, c.code as course_code, c.name as course_name
                       FROM courses c
                       JOIN course_outcomes co ON c.id = co.course_id
                       WHERE c.faculty_id = ?
                       ORDER BY c.code");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $courses = $stmt->get_result();

    while ($course = $courses->fetch_assoc()):
    ?>
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $course['course_code'] . ' - ' . $course['course_name']; ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>CO Code</th>
                                <th>CO Description</th>
                                <th>Attainment %</th>
                                <th>Target</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt2 = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
                            $stmt2->bind_param("i", $course['id']);
                            $stmt2->execute();
                            $outcomes = $stmt2->get_result();

                            while ($co = $outcomes->fetch_assoc()):
                                $attainment = calculateAttainment($co['id'], $course['id']);
                                $is_attained = $attainment >= 60;
                            ?>
                            <tr>
                                <td><strong><?php echo $co['code']; ?></strong></td>
                                <td><?php echo substr($co['description'], 0, 50); ?>...</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?php echo $is_attained ? 'success' : 'danger'; ?>"
                                             style="width: <?php echo $attainment; ?>%">
                                            <?php echo round($attainment, 2); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>60%</td>
                                <td>
                                    <span class="badge bg-<?php echo $is_attained ? 'success' : 'danger'; ?>">
                                        <?php echo $is_attained ? 'Attained' : 'Not Attained'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
                            endwhile;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    endwhile;
    ?>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Attainment Summary</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM course_outcomes WHERE course_id IN
                                   (SELECT id FROM courses WHERE faculty_id = ?)");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $total = $stmt->get_result()->fetch_assoc();

                $attained = 0;
                $stmt = $db->prepare("SELECT DISTINCT c.id FROM courses c WHERE c.faculty_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $courses = $stmt->get_result();

                while ($course = $courses->fetch_assoc()) {
                    $stmt2 = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ?");
                    $stmt2->bind_param("i", $course['id']);
                    $stmt2->execute();
                    $outcomes = $stmt2->get_result();
                    while ($co = $outcomes->fetch_assoc()) {
                        if (calculateAttainment($co['id'], $course['id']) >= 60) {
                            $attained++;
                        }
                    }
                }

                $percentage = $total['total'] > 0 ? ($attained / $total['total']) * 100 : 0;
                ?>
                <h3><?php echo round($percentage, 2); ?>%</h3>
                <p class="text-muted">Overall Attainment</p>
                <hr>
                <p>Total COs: <?php echo $total['total']; ?></p>
                <p>Attained COs: <?php echo $attained; ?></p>
                <p>Not Attained: <?php echo $total['total'] - $attained; ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recommendations</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if ($percentage < 60): ?>
                    <li class="list-group-item text-danger">
                        <i class="fas fa-exclamation-circle"></i> Many COs are not meeting attainment targets
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-lightbulb"></i> Review assessment questions and Bloom's levels
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-book"></i> Consider additional teaching resources
                    </li>
                    <?php else: ?>
                    <li class="list-group-item text-success">
                        <i class="fas fa-check-circle"></i> Good overall attainment
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-star"></i> Continue maintaining current teaching strategies
                    </li>
                    <?php endif; ?>
                    <li class="list-group-item">
                        <i class="fas fa-chart-pie"></i> Monitor progress regularly
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-comments"></i> Collect student feedback
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
