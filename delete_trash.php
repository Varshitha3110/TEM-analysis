<?php
header('Content-Type: application/json');

// Accept POST only
if(!isset($_POST['file'])) {
    echo json_encode(['status'=>'error','message'=>'No file specified']);
    exit;
}

$file = urldecode($_POST['file']);
$trashFolder = __DIR__ . '/uploads/trash';

// Split filename
$baseName = pathinfo($file, PATHINFO_FILENAME);
$extension = pathinfo($file, PATHINFO_EXTENSION);

// Match any file starting with base name
$matches = glob($trashFolder . '/' . $baseName . '*.*');

if(count($matches) === 0){
    echo json_encode(['status'=>'error','message'=>"File does not exist in trash"]);
    exit;
}

$deleted = [];
$failed = [];

foreach($matches as $f){
    if(unlink($f)) $deleted[] = basename($f);
    else $failed[] = basename($f);
}

$response = [];
if(!empty($deleted)){
    $response['status'] = 'success';
    $response['deleted_files'] = $deleted;
    $response['message'] = 'File(s) deleted successfully';
}
if(!empty($failed)){
    $response['status'] = 'partial';
    $response['failed_files'] = $failed;
    $response['message'] = 'Some files could not be deleted';
}
if(empty($deleted) && empty($failed)){
    $response['status'] = 'error';
    $response['message'] = 'No files deleted';
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
