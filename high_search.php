<?php
header("Content-Type: high_application/json");

$query = strtolower($_GET['q'] ?? '');
$results = [];

$csvFolder = "high_uploads/excel_files/";
$pdfFolder = "high_uploads/jpg_files/";

$allFiles = [];

// Collect CSV files
if (is_dir($csvFolder)) {
    foreach (scandir($csvFolder) as $f) {
        if ($f !== "." && $f !== ".." && in_array(pathinfo($f, PATHINFO_EXTENSION), ["csv", "xlsx", "xls"])) {
            $allFiles[] = $f;
        }
    }
}

// Collect PDF files
if (is_dir($pdfFolder)) {
    foreach (scandir($pdfFolder) as $f) {
        if ($f !== "." && $f !== ".." && pathinfo($f, PATHINFO_EXTENSION) === "png") {
            $allFiles[] = $f;
        }
    }
}

// Filter by query
foreach ($allFiles as $file) {
    if ($query === '' || strpos(strtolower($file), $query) !== false) {
        $results[] = $file;
    }
}

echo json_encode(array_values($results));
