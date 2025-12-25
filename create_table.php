<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

$sql = "CREATE TABLE IF NOT EXISTS co_attainment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    co_id INT NOT NULL,
    assessment_id INT NOT NULL,
    attainment_percentage DECIMAL(5,2),
    is_attained TINYINT(1) DEFAULT 0,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    target_threshold DECIMAL(5,2) DEFAULT 60.00,
    FOREIGN KEY (co_id) REFERENCES course_outcomes(id),
    FOREIGN KEY (assessment_id) REFERENCES assessments(id),
    UNIQUE KEY unique_co_assessment (co_id, assessment_id)
)";

if ($db->query($sql) === TRUE) {
    echo "Table co_attainment created successfully";
} else {
    echo "Error creating table: " . $db->error;
}
?>
