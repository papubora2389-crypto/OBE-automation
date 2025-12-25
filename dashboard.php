<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$db = getDB();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
</div>

<?php if (hasRole('admin')): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Programs</h6>
                    <?php $res = $db->query("SELECT COUNT(*) as cnt FROM programs"); $row = $res->fetch_assoc(); ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Courses</h6>
                    <?php $res = $db->query("SELECT COUNT(*) as cnt FROM courses"); $row = $res->fetch_assoc(); ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Faculty</h6>
                    <?php $res = $db->query("SELECT COUNT(*) as cnt FROM users WHERE role='faculty'"); $row = $res->fetch_assoc(); ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Students</h6>
                    <?php $res = $db->query("SELECT COUNT(*) as cnt FROM users WHERE role='student'"); $row = $res->fetch_assoc(); ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <a href="admin/programs.php" class="btn btn-primary me-2"><i class="fas fa-book"></i> Manage Programs</a>
            <a href="admin/courses.php" class="btn btn-success me-2"><i class="fas fa-chalkboard"></i> Manage Courses</a>
            <a href="admin/users.php" class="btn btn-info"><i class="fas fa-users"></i> Manage Users</a>
        </div>
    </div>

<?php elseif (hasRole('faculty')): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">My Courses</h6>
                    <?php
                    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM courses WHERE faculty_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Assessments</h6>
                    <?php
                    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM assessments a
                                        JOIN courses c ON a.course_id = c.id
                                        WHERE c.faculty_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Question Papers</h6>
                    <?php
                    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM question_papers WHERE created_by = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Reports</h6>
                    <a href="faculty/reports.php" class="btn btn-light">View Reports</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">My Courses</div>
        <div class="card-body">
            <?php
            $stmt = $db->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY semester");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($course = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $course['code']; ?></td>
                        <td><?php echo $course['name']; ?></td>
                        <td><?php echo $course['semester']; ?></td>
                        <td>
                            <a href="faculty/course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>

<?php elseif (hasRole('student')): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Enrolled Courses</h6>
                    <?php
                    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Completed Assessments</h6>
                    <?php
                    $stmt = $db->prepare("SELECT COUNT(DISTINCT sm.assessment_id) as cnt
                                        FROM student_marks sm
                                        JOIN enrollments e ON sm.enrollment_id = e.id
                                        WHERE e.student_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    ?>
                    <h3><?php echo $row['cnt']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Average Performance</h6>
                    <?php
                    $stmt = $db->prepare("SELECT AVG((sm.marks_obtained / a.max_marks) * 100) as avg
                                        FROM student_marks sm
                                        JOIN assessments a ON sm.assessment_id = a.id
                                        JOIN enrollments e ON sm.enrollment_id = e.id
                                        WHERE e.student_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $avg = round($row['avg'] ?? 0, 2);
                    ?>
                    <h3><?php echo $avg; ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">My Courses</div>
        <div class="card-body">
            <?php
            $stmt = $db->prepare("SELECT c.* FROM enrollments e
                                JOIN courses c ON e.course_id = c.id
                                WHERE e.student_id = ?
                                ORDER BY c.semester");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($course = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $course['code']; ?></td>
                        <td><?php echo $course['name']; ?></td>
                        <td><?php echo $course['semester']; ?></td>
                        <td>
                            <a href="student/course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
