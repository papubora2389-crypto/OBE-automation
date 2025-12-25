<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];
$paper_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT qp.*, c.code, c.name FROM question_papers qp
                     JOIN courses c ON qp.course_id = c.id
                     WHERE qp.id = ? AND qp.created_by = ?");
$stmt->bind_param("ii", $paper_id, $user_id);
$stmt->execute();
$paper = $stmt->get_result()->fetch_assoc();

if (!$paper) {
    header("Location: question-papers.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM question_paper_questions WHERE paper_id = ? ORDER BY display_order");
$stmt->bind_param("i", $paper_id);
$stmt->execute();
$questions = $stmt->get_result();

include '../includes/header.php';
?>

<style>
@media print {
    @page {
        margin: 0;
        size: auto;
    }

    /* Hide browser header/footer content */
    html, body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide sidebar and navigation */
    .sidebar, nav.sidebar, .navbar, nav.navbar {
        display: none !important;
    }
    /* Hide everything except main content area */
    body > .container-fluid > .row > nav {
        display: none !important;
    }
    /* Make main content full width */
    main {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        position: relative !important;
    }
    .row, .col-md-11, .card, .card-body {
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }
    .card-body {
        padding: 10px !important;
        position: relative !important;
        top: 0 !important;
        left: 0 !important;
    }
    .no-print {
        display: none !important;
    }
    body {
        font-size: 12pt !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .badge {
        border: 1px solid #000 !important;
        background: white !important;
        color: black !important;
    }
    /* Ensure tables print properly */
    .table {
        margin-bottom: 0 !important;
    }
    /* Remove any inherited margins */
    * {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    /* Hide footer */
    footer {
        display: none !important;
    }

    /* Decrease font size of marks */
    .questions-section .col-1 strong,
    .table td strong {
        font-size: 10pt !important;
    }

    /* Remove coloring from badges */
    .badge {
        background-color: transparent !important;
        background: none !important;
        color: black !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        font-weight: normal !important;
        box-shadow: none !important;
    }

    /* Specific spacing for content */
    .text-center {
        margin-bottom: 10px !important;
    }
    .mb-4, .mb-3 {
        margin-bottom: 10px !important;
    }

    /* Hide page header and footer in some browsers */
    @page :first {
        margin-top: 0;
    }
    @page :left {
        margin-left: 0;
    }
    @page :right {
        margin-right: 0;
    }
}
</style>

<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card">
            <div class="card-body p-5" style="font-family: 'Times New Roman', serif;">
                <!-- Header Section -->
                <div class="text-center mb-4">
                    <h4 class="mb-3"><strong>Tezpur University</strong></h4>
                    <h5><?php echo $paper['exam_type']; ?> - <?php echo $paper['academic_year']; ?></h5>
                </div>

                <!-- Course Details -->
                <div class="row mb-4">
                    <div class="col-6">
                        <strong>Course Code:</strong> <?php echo $paper['code']; ?>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Full Marks:</strong> <?php echo $paper['total_marks']; ?>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <strong>Course Title:</strong> <?php echo $paper['name']; ?>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Time:</strong> <?php echo $paper['duration']; ?> mins
                    </div>
                </div>

                <hr style="border-top: 2px solid #000;">

                <!-- Instructions -->
                <div class="mb-4">
                    <p style="font-style: italic;">(The figures in the right-hand margin indicate full marks for the questions)</p>
                </div>

                <!-- Questions Section -->
                <div class="questions-section">
                    <?php
                    $questions->data_seek(0);
                    $question_count = 0;
                    $total_marks = 0;
                    while ($q = $questions->fetch_assoc()):
                        $question_count++;
                        $total_marks += $q['marks'];
                    ?>
                    <div class="row mb-4">
                        <div class="col-11">
                            <p class="mb-1">
                                <strong>Q<?php echo $question_count; ?>.</strong>
                                <?php echo nl2br(htmlspecialchars($q['question_text'])); ?>
                            </p>
                            <?php if (!empty($q['diagram_path']) && file_exists('../' . $q['diagram_path'])): ?>
                                <div class="mt-2 text-center">
                                    <img src="../<?php echo $q['diagram_path']; ?>" alt="Question Diagram" class="img-fluid" style="max-width: 400px; max-height: 300px; display: block; margin: 0 auto;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-1 text-end">
                            <strong><?php echo $q['marks']; ?></strong>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- End of Paper -->
                <div class="text-center mt-5 mb-5">
                    <p><strong>******************** END OF PAPER ********************</strong></p>
                </div>

                <!-- Page Break for Print -->
                <div style="page-break-before: always;"></div>

                <!-- Quick CO Mapping Table -->
                <div class="mt-5">
                    <h4 class="mb-3">Quick CO Mapping</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="table-light"><strong>Q No.</strong></td>
                                    <?php
                                    $questions->data_seek(0);
                                    $question_count = 0;
                                    while ($q = $questions->fetch_assoc()):
                                        $question_count++;
                                    ?>
                                    <td class="table-light"><strong>Q<?php echo $question_count; ?></strong></td>
                                    <?php endwhile; ?>
                                </tr>
                                <tr>
                                    <td class="table-light"><strong>CO(s)</strong></td>
                                    <?php
                                    $questions->data_seek(0);
                                    while ($q = $questions->fetch_assoc()):
                                        $co_code = '-';
                                        if ($q['co_id']) {
                                            $co_stmt = $db->prepare("SELECT code FROM course_outcomes WHERE id = ?");
                                            $co_stmt->bind_param("i", $q['co_id']);
                                            $co_stmt->execute();
                                            $co = $co_stmt->get_result()->fetch_assoc();
                                            $co_code = $co['code'] ?? '-';
                                        }
                                    ?>
                                    <td><?php echo $co_code; ?></td>
                                    <?php endwhile; ?>
                                </tr>
                                <tr>
                                    <td class="table-light"><strong>Bloom's Level</strong></td>
                                    <?php
                                    $questions->data_seek(0);
                                    while ($q = $questions->fetch_assoc()):
                                        $bloom_label = bloomLevelLabel(intval($q['bloom_level']));
                                    ?>
                                    <td>L<?php echo $q['bloom_level']; ?></td>
                                    <?php endwhile; ?>
                                </tr>
                                <tr>
                                    <td class="table-light"><strong>Marks</strong></td>
                                    <?php
                                    $questions->data_seek(0);
                                    while ($q = $questions->fetch_assoc()):
                                    ?>
                                    <td><strong><?php echo $q['marks']; ?></strong></td>
                                    <?php endwhile; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Course Outcomes Summary Table -->
                <div class="mt-5">
                    <h4 class="mb-3">Course Outcomes Summary</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Course Outcomes</th>
                                    <th>Bloom's Level</th>
                                    <th>Description</th>
                                    <th>Marks Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch all course outcomes for this course
                                $co_stmt = $db->prepare("SELECT co.* FROM course_outcomes co
                                                        JOIN courses c ON co.course_id = c.id
                                                        JOIN question_papers qp ON qp.course_id = c.id
                                                        WHERE qp.id = ?
                                                        ORDER BY co.code");
                                $co_stmt->bind_param("i", $paper_id);
                                $co_stmt->execute();
                                $course_outcomes = $co_stmt->get_result();

                                // Calculate marks distribution per CO and Bloom's level
                                $questions->data_seek(0);
                                $co_marks = [];
                                $co_bloom_marks = [];

                                while ($q = $questions->fetch_assoc()) {
                                    if ($q['co_id']) {
                                        // Get CO code
                                        $co_code_stmt = $db->prepare("SELECT code FROM course_outcomes WHERE id = ?");
                                        $co_code_stmt->bind_param("i", $q['co_id']);
                                        $co_code_stmt->execute();
                                        $co_data = $co_code_stmt->get_result()->fetch_assoc();
                                        $co_code = $co_data['code'];

                                        // Total marks per CO
                                        if (!isset($co_marks[$co_code])) {
                                            $co_marks[$co_code] = 0;
                                        }
                                        $co_marks[$co_code] += $q['marks'];

                                        // Marks per CO per Bloom's level
                                        $bloom_key = 'L' . $q['bloom_level'];
                                        if (!isset($co_bloom_marks[$co_code])) {
                                            $co_bloom_marks[$co_code] = [];
                                        }
                                        if (!isset($co_bloom_marks[$co_code][$bloom_key])) {
                                            $co_bloom_marks[$co_code][$bloom_key] = 0;
                                        }
                                        $co_bloom_marks[$co_code][$bloom_key] += $q['marks'];
                                    }
                                }

                                while ($co = $course_outcomes->fetch_assoc()):
                                    $co_code = $co['code'];
                                    $total_co_marks = $co_marks[$co_code] ?? 0;

                                    // Get primary Bloom's level for this CO (most used)
                                    $primary_bloom = '-';
                                    if (isset($co_bloom_marks[$co_code])) {
                                        $max_marks = 0;
                                        foreach ($co_bloom_marks[$co_code] as $bloom => $marks) {
                                            if ($marks > $max_marks) {
                                                $max_marks = $marks;
                                                $primary_bloom = $bloom;
                                            }
                                        }
                                    }

                                    // Get Bloom's level description
                                    $bloom_desc = '-';
                                    if ($primary_bloom !== '-') {
                                        $level_num = intval(str_replace('L', '', $primary_bloom));
                                        $bloom_desc = bloomLevelLabel($level_num);
                                    }
                                ?>
                                <tr>
                                    <td><strong><?php echo $co_code; ?></strong></td>
                                    <td><?php echo htmlspecialchars($co['description']); ?></td>

                                    <td><?php echo $co['bloom_level']; ?></td>
                                    <td><?php echo bloomLevelLabel(intval($co['bloom_level'])); ?></td>
                                    <td>
                                        <strong><?php echo $total_co_marks; ?> marks</strong>
                                        <?php if (isset($co_bloom_marks[$co_code])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php
                                            $bloom_details = [];
                                            foreach ($co_bloom_marks[$co_code] as $bloom => $marks) {
                                                $bloom_details[] = "$bloom: $marks";
                                            }
                                            echo implode(', ', $bloom_details);
                                            ?>
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="table-secondary">
                                    <td colspan="4" class="text-end"><strong>Total Marks</strong></td>
                                    <td><strong><?php echo $total_marks; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-5 text-center no-print">
                    <button onclick="window.print()" class="btn btn-success">
                        <i class="fas fa-print"></i> Print Question Paper
                    </button>
                    <a href="edit-question-paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="question-papers.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
