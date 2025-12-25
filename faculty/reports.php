<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireRole('faculty');

$db = getDB();
$user = getCurrentUser();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Reports</h2>
</div>

<ul class="nav nav-tabs" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="assessments-tab" data-bs-toggle="tab" data-bs-target="#assessments" type="button" role="tab" aria-controls="assessments" aria-selected="true">Student Assessments</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="blooms-tab" data-bs-toggle="tab" data-bs-target="#blooms" type="button" role="tab" aria-controls="blooms" aria-selected="false">Bloom's Distribution</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="papers-tab" data-bs-toggle="tab" data-bs-target="#papers" type="button" role="tab" aria-controls="papers" aria-selected="false">Question Paper Analysis</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">Course Files Summary</button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">
    <div class="tab-pane fade show active" id="assessments" role="tabpanel" aria-labelledby="assessments-tab">
        <div class="card mt-3">
            <div class="card-header">Student Assessments Report</div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT u.full_name as student_name, u.username as enrollment_id, c.name as course_name, a.name as assessment_name, a.type, a.max_marks, sm.marks_obtained, ROUND((sm.marks_obtained / a.max_marks) * 100, 2) as percentage FROM student_marks sm JOIN assessments a ON sm.assessment_id = a.id JOIN enrollments e ON sm.enrollment_id = e.id JOIN users u ON e.student_id = u.id JOIN courses c ON a.course_id = c.id WHERE c.faculty_id = ? ORDER BY c.name, a.name, u.full_name");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive"><table class="table table-striped"><tr><th>Student Name</th><th>Enrollment ID</th><th>Course</th><th>Assessment</th><th>Type</th><th>Max Marks</th><th>Marks Obtained</th></tr>';
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>{$row['student_name']}</td><td>{$row['enrollment_id']}</td><td>{$row['course_name']}</td><td>{$row['assessment_name']}</td><td>{$row['type']}</td><td>{$row['max_marks']}</td><td>{$row['marks_obtained']}</td></tr>";
                    }
                    echo '</table></div>';
                } else {
                    echo '<p>No assessment data available.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="blooms" role="tabpanel" aria-labelledby="blooms-tab">
        <div class="card mt-3">
            <div class="card-header">Bloom's Taxonomy Distribution</div>
            <div class="card-body">
                <?php
                $bloom_levels = [1 => 'Remembering', 2 => 'Understanding', 3 => 'Applying', 4 => 'Analyzing', 5 => 'Evaluating', 6 => 'Creating'];
                $stmt = $db->prepare("SELECT bloom_level, COUNT(*) as count FROM question_paper_questions qpq JOIN question_papers qp ON qpq.paper_id = qp.id JOIN courses c ON qp.course_id = c.id WHERE c.faculty_id = ? GROUP BY bloom_level ORDER BY bloom_level");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $total = 0;
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[$row['bloom_level']] = $row['count'];
                    $total += $row['count'];
                }
                if ($total > 0) {
                    echo '<div class="table-responsive"><table class="table table-striped"><tr><th>Level</th><th>Name</th><th>Count</th><th>Percentage</th></tr>';
                    foreach ($bloom_levels as $level => $name) {
                        $count = $data[$level] ?? 0;
                        $percentage = $total > 0 ? round(($count / $total) * 100, 2) : 0;
                        echo "<tr><td>$level</td><td>$name</td><td>$count</td><td>$percentage%</td></tr>";
                    }
                    echo '</table></div>';
                } else {
                    echo '<p>No question data available.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="papers" role="tabpanel" aria-labelledby="papers-tab">
        <div class="card mt-3">
            <div class="card-header">Question Paper Analysis</div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT qp.title, qp.total_marks, COUNT(qpq.id) as total_questions, SUM(CASE WHEN qpq.bloom_level = 1 THEN 1 ELSE 0 END) as remembering, SUM(CASE WHEN qpq.bloom_level = 2 THEN 1 ELSE 0 END) as understanding, SUM(CASE WHEN qpq.bloom_level = 3 THEN 1 ELSE 0 END) as applying, SUM(CASE WHEN qpq.bloom_level = 4 THEN 1 ELSE 0 END) as analyzing, SUM(CASE WHEN qpq.bloom_level = 5 THEN 1 ELSE 0 END) as evaluating, SUM(CASE WHEN qpq.bloom_level = 6 THEN 1 ELSE 0 END) as creating FROM question_papers qp LEFT JOIN question_paper_questions qpq ON qp.id = qpq.paper_id JOIN courses c ON qp.course_id = c.id WHERE c.faculty_id = ? GROUP BY qp.id ORDER BY qp.created_at DESC");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive"><table class="table table-striped"><tr><th>Paper Title</th><th>Total Marks</th><th>Total Questions</th><th>Remembering</th><th>Understanding</th><th>Applying</th><th>Analyzing</th><th>Evaluating</th><th>Creating</th></tr>';
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>{$row['title']}</td><td>{$row['total_marks']}</td><td>{$row['total_questions']}</td><td>{$row['remembering']}</td><td>{$row['understanding']}</td><td>{$row['applying']}</td><td>{$row['analyzing']}</td><td>{$row['evaluating']}</td><td>{$row['creating']}</td></tr>";
                    }
                    echo '</table></div>';
                } else {
                    echo '<p>No question papers available.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
        <div class="card mt-3">
            <div class="card-header">Course Files Summary</div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT cf.file_name, cf.file_type, cf.upload_date, cf.description, c.name as course_name FROM course_files cf JOIN courses c ON cf.course_id = c.id WHERE c.faculty_id = ? ORDER BY cf.upload_date DESC");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive"><table class="table table-striped"><tr><th>File Name</th><th>Type</th><th>Course</th><th>Upload Date</th><th>Description</th></tr>';
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>{$row['file_name']}</td><td>{$row['file_type']}</td><td>{$row['course_name']}</td><td>{$row['upload_date']}</td><td>{$row['description']}</td></tr>";
                    }
                    echo '</table></div>';
                } else {
                    echo '<p>No course files uploaded.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>



<?php include '../includes/footer.php'; ?>
