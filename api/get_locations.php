<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

$parentId = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : null;
$level = isset($_GET['level']) ? $_GET['level'] : 'province';

// Optimized query for hierarchy fetching
if ($level === 'province') {
    $stmt = $conn->prepare("SELECT id, name FROM administrative_divisions WHERE level = 'province' ORDER BY name ASC");
} else {
    $stmt = $conn->prepare("SELECT id, name FROM administrative_divisions WHERE parent_id = ? AND level = ? ORDER BY name ASC");
    $stmt->bind_param("is", $parentId, $level);
}

$stmt->execute();
$result = $stmt->get_result();
$locations = [];
while ($row = $result->fetch_assoc()) {
    $row['verified'] = true; // Every data point in our DB is "Nationally Valid"
    $locations[] = $row;
}

echo json_encode($locations);
$conn->close();
?>