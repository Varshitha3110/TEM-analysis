<?php
header('Content-Type: application/json');


// --- Setup uploads folder ---
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

// --- Handle uploaded images ---
$uploaded_files = [];
if (isset($_FILES['images'])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $filename = basename($_FILES['images']['name'][$key]);
        $target_file = $upload_dir . $filename;

// Check if file already exists
if (file_exists($target_file)) {
    // Option 1: skip the file
    // continue;

    // Option 2: rename the file to avoid collision
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $counter = 1;
    while (file_exists($upload_dir . $name . "_$counter." . $ext)) {
        $counter++;
    }
    $filename = $name . "_$counter." . $ext;
    $target_file = $upload_dir . $filename;
}

// Now move the uploaded file
if (move_uploaded_file($tmp_name, $target_file)) {
    $uploaded_files[] = $filename;
}

    }
}

// --- Get scale and thresholds ---
$scale = isset($_POST['scale']) ? floatval($_POST['scale']) : 1.0;
$thresholds = isset($_POST['thresholds']) ? json_decode($_POST['thresholds'], true) : array_fill(0, count($uploaded_files), 128);

// --- Save config for Python ---
$config = [
    'scale' => $scale,
    'thresholds' => $thresholds,
    'files' => $uploaded_files
];
$config_file = $upload_dir . 'config.json';
file_put_contents($config_file, json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

// --- Execute Python script ---
$python = 'C:/Python313/python.exe';
$script = 'C:/xampp/htdocs/application/set2.py';
$cmd = "\"$python\" \"$script\" \"$config_file\" \"$upload_dir\" 2>&1";

$output = [];
$return_var = 0;
exec($cmd, $output, $return_var);

// --- Gather generated Excel/CSV, PDF, and Parameters files ---
$excel_dir = $upload_dir . "excel_files/";
$pdf_dir = $upload_dir . "pdf_files/";
$params_dir = $upload_dir . "parameters/";

$excel_files = [];
$pdf_files = [];
$params_files = [];

foreach ($uploaded_files as $file) {
    $base = pathinfo($file, PATHINFO_FILENAME);

    $xls = $excel_dir . $base . "_aggregates.xlsx";
    $pdf = $pdf_dir . $base . ".pdf";
    $param = $params_dir . $base . "_parameters.xlsx";

    if (file_exists($xls)) $excel_files[] = $xls;
    if (file_exists($pdf)) $pdf_files[] = $pdf;
    if (file_exists($param)) $params_files[] = $param;
}

// --- Prepare response ---
$response = [
    'status' => $return_var === 0 ? 'success' : 'error',
    'output' => array_map('utf8_encode', $output),
    'csv_files' => array_map('basename', $excel_files),
        'uploaded_files' => $uploaded_files,  // <--- ADD THIS

    'pdf_files' => array_map('basename', $pdf_files),
    'param_files' => array_map('basename', $params_files)
];

echo json_encode($response, JSON_UNESCAPED_SLASHES);
exit;
