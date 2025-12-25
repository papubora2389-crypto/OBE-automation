<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];

$result = $db->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY semester");
$result->bind_param("i", $user_id);
$result->execute();
$courses = $result->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-chalkboard"></i> My Courses</h2>

<div class="row">
    <?php while ($course = $courses->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo $course['code']; ?></h5>
                <h6 class="card-subtitle text-muted"><?php echo $course['name']; ?></h6>
                <p class="mt-3 mb-2">
                    <i class="fas fa-layer-group"></i> Semester: <?php echo $course['semester']; ?>
                </p>
                <p class="mb-2">
                    <i class="fas fa-credit-card"></i> Credits: <?php echo $course['credits']; ?>
                </p>
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE course_id = ?");
                $stmt->bind_param("i", $course['id']);
                $stmt->execute();
                $enroll = $stmt->get_result()->fetch_assoc();
                ?>
                <p class="mb-3">
                    <i class="fas fa-users"></i> Students: <?php echo $enroll['cnt']; ?>
                </p>
            </div>
            <div class="card-footer bg-transparent">
                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>
