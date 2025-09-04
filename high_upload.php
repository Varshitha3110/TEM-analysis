<?php
header('Content-Type: application/json');
error_reporting(0);
ob_start(); // prevent any extra output

$response = [];

try {
    // === Check for uploaded files ===
    if (!isset($_FILES['images'])) {
        throw new Exception("No files uploaded.");
    }

    $upload_dir = __DIR__ . '/high_uploads/';
    $jpg_dir    = $upload_dir . 'jpg_files/';
    $excel_dir  = $upload_dir . 'excel_files/';
    $param_dir  = $upload_dir . 'parameters_files/';

    // Create necessary directories
    foreach ([$upload_dir, $jpg_dir, $excel_dir, $param_dir] as $d) {
        if (!file_exists($d)) mkdir($d, 0777, true);
    }

    $uploaded_files = [];
    foreach ($_FILES['images']['tmp_name'] as $i => $tmp_name) {
        $filename = basename($_FILES['images']['name'][$i]);
        $target = $upload_dir . $filename;
        if (move_uploaded_file($tmp_name, $target)) {
            $uploaded_files[] = $filename;
        }
    }

    if (empty($uploaded_files)) {
        throw new Exception("Failed to move uploaded files.");
    }

    // === Prepare config JSON ===
    $config_path = $upload_dir . 'config.json';
    file_put_contents($config_path, json_encode(['files' => $uploaded_files]));

    // === Execute Python script ===
    $python_script = __DIR__ . '/set1.py';
    $cmd = escapeshellcmd("python \"$python_script\" \"$config_path\"");
    
    $output = [];
    $return_var = 0;
    exec($cmd . " 2>&1", $output, $return_var);
    $output_text = implode("\n", $output);

    if ($return_var !== 0) {
        throw new Exception("Python script error:\n" . $output_text);
    }

    // === Parse Python JSON output ===
    $data = json_decode($output_text, true);
    if ($data === null) {
        throw new Exception("Invalid JSON returned from Python:\n" . $output_text);
    }

    // === Prepare response for JS (convert to web-accessible paths) ===
    $csv_files = [];
    $images    = [];
    $params    = [];

    $base_web_path = "high_uploads/"; // relative to your web root

    foreach ($data as $item) {
        // CSV
        if (isset($item['csv'])) {
            $csv_files[] = $base_web_path . "excel_files/" . basename($item['csv']);
        }

        // Images
        if (isset($item['images'])) {
            foreach ($item['images'] as $img) {
                $images[] = $base_web_path . "jpg_files/" . basename($img);
            }
        }

        // Parameters
// Parameters (Excel files)
if (isset($item['csv'])) {
    $base_name = pathinfo($item['csv'], PATHINFO_FILENAME); 
    $xlsx_name = $base_name . ".xlsx";
    $params[$xlsx_name] = $base_web_path . "parameters_files/" . $xlsx_name;
}


    }

    $response = [
        'status' => 'success',
        'python_output' => "Processed " . count($uploaded_files) . " files.",
        'csv_files' => $csv_files,
        'images'    => $images,
        'params'    => $params
    ];

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// === Return JSON ===
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
