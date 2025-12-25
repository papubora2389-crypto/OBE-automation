<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$db = getDB();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    $course_id_to_enroll = intval($_POST['course_id']);
    // Prevent duplicate enrollment
    $check_stmt = $db->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $check_stmt->bind_param("ii", $user_id, $course_id_to_enroll);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        $insert_stmt = $db->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_date) VALUES (?, ?, NOW())");
        $insert_stmt->bind_param("ii", $user_id, $course_id_to_enroll);
        $insert_stmt->execute();
    }
}

$stmt = $db->prepare("SELECT c.* FROM enrollments e
                     JOIN courses c ON e.course_id = c.id
                     WHERE e.student_id = ? ORDER BY c.semester");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch courses student is not enrolled in
$not_enrolled_stmt = $db->prepare("SELECT * FROM courses WHERE id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)");
$not_enrolled_stmt->bind_param("i", $user_id);
$not_enrolled_stmt->execute();
$not_enrolled_courses = $not_enrolled_stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-book"></i> My Courses</h2>

<div class="row">
    <?php while ($course = $result->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo $course['code']; ?></h5>
                <h6 class="card-subtitle text-muted"><?php echo $course['name']; ?></h6>
                <p class="mt-3 mb-2">
                    <i class="fas fa-layer-group"></i> Semester: <?php echo $course['semester']; ?>
                </p>
                <p class="mb-3">
                    <i class="fas fa-credit-card"></i> Credits: <?php echo $course['credits']; ?></p>
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

<h2 class="mb-4 mt-5"><i class="fas fa-plus-circle"></i> Available Courses</h2>

<div class="row">
    <?php while ($course = $not_enrolled_courses->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border border-success">
            <div class="card-body">
                <h5 class="card-title"><?php echo $course['code']; ?></h5>
                <h6 class="card-subtitle text-muted"><?php echo $course['name']; ?></h6>
                <p class="mt-3 mb-2">
                    <i class="fas fa-layer-group"></i> Semester: <?php echo $course['semester']; ?>
                </p>
                <p class="mb-3">
                    <i class="fas fa-credit-card"></i> Credits: <?php echo $course['credits']; ?></p>
            </div>
            <div class="card-footer bg-transparent">
                <form method="POST" onsubmit="return confirm('Enroll in this course?');">
                    <input type="hidden" name="action" value="enroll">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-plus"></i> Enroll
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>
