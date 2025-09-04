<?php
header('Content-Type: application/json');

// Get type and file from GET
$type = $_GET['type'] ?? '';
$fileName = $_GET['file'] ?? '';
$fileName = basename($fileName); // sanitize

if (!$fileName || !$type) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Define main folders
$folders = [
    'image' => ['uploads/', 'uploads/trash/'],
    'pdf'   => ['uploads/pdf_files/', 'uploads/trash/'],
    'excel' => ['uploads/excel_files/', 'uploads/trash/']
];

// Validate type
if (!isset($folders[$type])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
    exit;
}

// Search in main and trash folders
$paths = $folders[$type];
$found = null;
foreach ($paths as $p) {
    if (file_exists($p . $fileName)) {
        $found = $p . $fileName;
        break;
    }
}

if ($found) {
    echo json_encode(['status' => 'success', 'path' => $found]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'File not found']);
}
?>
