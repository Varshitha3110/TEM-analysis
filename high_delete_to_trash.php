<?php
// high_delete_to_trash.php

header('Content-Type: application/json');

// Base folders
$uploadFolders = [
    'excel' => 'high_uploads/excel_files/',
    'images' => 'high_uploads/jpg_files/',
    'pdf' => 'high_uploads/pdf_files/'
];
$trashFolder = 'high_uploads/trash/';

// Ensure trash folder exists
if (!is_dir($trashFolder)) {
    mkdir($trashFolder, 0777, true);
}

// Check if 'file' parameter is provided
if (!isset($_GET['file']) || !isset($_GET['type'])) {
    echo json_encode(['status' => 'error', 'message' => 'File or type not specified']);
    exit;
}

$file = $_GET['file'];
$type = $_GET['type']; // 'excel', 'images', 'pdf'

// Determine source folder
if (!isset($uploadFolders[$type])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
    exit;
}

$sourceFolder = $uploadFolders[$type];
$sourcePath = $sourceFolder . $file;
$destPath = $trashFolder . $file;

if (!file_exists($sourcePath)) {
    echo json_encode(['status' => 'error', 'message' => 'File does not exist']);
    exit;
}

// Move file to trash
if (rename($sourcePath, $destPath)) {
    echo json_encode(['status' => 'success', 'message' => "$file moved to trash"]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move file to trash']);
}
