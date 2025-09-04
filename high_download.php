<?php
if (!isset($_GET['file'])) {
    die("No file specified.");
}

$fileName = urldecode($_GET['file']);
$fileName = basename($fileName); // sanitize input

$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$baseName  = pathinfo($fileName, PATHINFO_FILENAME);

// Define folders for different file types
$folders = [
    'pdf'  => [__DIR__ . '/high_uploads/jpg_files/', __DIR__ . '/high_uploads/trash/'],
    'xlsx' => [__DIR__ . '/high_uploads/excel_files/', __DIR__ . '/high_uploads/trash/'],
    'xls'  => [__DIR__ . '/high_uploads/excel_files/', __DIR__ . '/high_uploads/trash/'],
    'csv'  => [__DIR__ . '/high_uploads/excel_files/', __DIR__ . '/high_uploads/trash/'],
    'jpg'  => [__DIR__ . '/high_uploads/jpg_files/', __DIR__ . '/high_uploads/trash/'],
    'jpeg' => [__DIR__ . '/high_uploads/jpg_files/', __DIR__ . '/high_uploads/trash/'],
    'png'  => [__DIR__ . '/high_uploads/jpg_files/', __DIR__ . '/high_uploads/trash/'],
    'gif'  => [__DIR__ . '/high_uploads/jpg_files/', __DIR__ . '/high_uploads/trash/'],
];

// Default folders if unknown type
$searchFolders = $folders[$extension] ?? [__DIR__ . '/high_uploads/', __DIR__ . '/high_uploads/trash/'];

// Determine content type
$contentTypes = [
    'pdf'  => 'application/pdf',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls'  => 'application/vnd.ms-excel',
    'csv'  => 'text/csv',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif'
];

$contentType = $contentTypes[$extension] ?? 'application/octet-stream';

// Search for the file in the defined folders
$found = false;
foreach ($searchFolders as $folder) {
    $path = $folder . $fileName;
    if (file_exists($path)) {
        $filepath = $path;
        $found = true;
        break;
    }
}

// If file not found, stop
if (!$found) {
    die("File not found: " . htmlspecialchars($fileName));
}

// Send headers and output file
header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
