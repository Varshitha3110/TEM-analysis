<?php
if (!isset($_GET['file'])) {
    die("No file specified.");
}

$fileName = urldecode($_GET['file']);
$fileName = basename($fileName);

$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$baseName  = pathinfo($fileName, PATHINFO_FILENAME);

switch($extension) {
    case 'pdf':
        $folders = [__DIR__ . '/uploads/pdf_files/', __DIR__ . '/uploads/trash/'];
        $contentType = 'application/pdf';
        break;
    case 'xlsx':
    case 'xls':
        $folders = [__DIR__ . '/uploads/excel_files/', __DIR__ . '/uploads/trash/'];
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        break;
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif':
        $folders = [__DIR__ . '/uploads/', __DIR__ . '/uploads/trash/'];
        $contentType = 'image/' . $extension;
        break;
    default:
        $folders = [__DIR__ . '/uploads/', __DIR__ . '/uploads/trash/'];
        $contentType = 'application/octet-stream';
}

$found = false;

foreach ($folders as $folder) {
    if ($extension === 'xlsx' || $extension === 'xls') {
        // Search for Excel files starting with the base name
        $matches = glob($folder . $baseName . '*.xls*');
        if (count($matches) > 0) {
            $filepath = $matches[0];
            $found = true;
            break;
        }
    } else {
        $path = $folder . $fileName;
        if (file_exists($path)) {
            $filepath = $path;
            $found = true;
            break;
        }
    }
}

if (!$found) {
    die("File not found in uploads or trash: " . htmlspecialchars($fileName));
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
