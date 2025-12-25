<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$db = getDB();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT sm.*, a.name, a.max_marks, c.code, c.name as course_name
                     FROM student_marks sm
                     JOIN assessments a ON sm.assessment_id = a.id
                     JOIN courses c ON a.course_id = c.id
                     JOIN enrollments e ON sm.enrollment_id = e.id
                     WHERE e.student_id = ?
                     ORDER BY c.code, a.name");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-star"></i> My Marks</h2>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Assessment</th>
                        <th>Marks Obtained</th>
                        <th>Max Marks</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
                    ?>
                    <tr>
                        <td><strong><?php echo $row['code']; ?></strong></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['marks_obtained']; ?></td>
                        <td><?php echo $row['max_marks']; ?></td>
                        <td><?php echo formatDate($row['created_at']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
