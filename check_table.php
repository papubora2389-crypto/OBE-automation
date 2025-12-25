<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

$result = $db->query("SHOW TABLES LIKE 'co_attainment'");
if ($result->num_rows > 0) {
    echo "Table co_attainment exists";
} else {
    echo "Table co_attainment does not exist";
}
?>
