<?php
header('Content-Type: application/json');

if(!isset($_GET['file'])){
    echo json_encode(['status'=>'error', 'message'=>'No file specified']);
    exit;
}

$file = basename($_GET['file']); // sanitize
$uploadRoot = __DIR__ . '/uploads';
$trashFolder = $uploadRoot . '/trash';
$baseName = pathinfo($file, PATHINFO_FILENAME);
$extension = pathinfo($file, PATHINFO_EXTENSION);

// Determine original file path
$originalPath = '';
// Excel search
if(in_array(strtolower($extension), ['xlsx','xls'])){
    $matches = glob("$uploadRoot/excel_files/$baseName*.xls*");
    if(count($matches) > 0){
        $originalPath = $matches[0];
    }
}
// PDF search
elseif(strtolower($extension) === 'pdf'){
    if(file_exists("$uploadRoot/pdf_files/$file")){
        $originalPath = "$uploadRoot/pdf_files/$file";
    }
}

if(empty($originalPath)){
    echo json_encode(['status'=>'error', 'message'=>'File does not exist in uploads']);
    exit;
}

// Create trash folder if not exists
if(!is_dir($trashFolder)) mkdir($trashFolder, 0777, true);

// Move file to trash
$deletedPath = $trashFolder . '/' . basename($originalPath);
if(rename($originalPath, $deletedPath)){
    echo json_encode(['status'=>'success', 'message'=>'File moved to trash']);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Failed to move file to trash']);
}
?>
