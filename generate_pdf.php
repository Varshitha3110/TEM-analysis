<?php
require('fpdf.php');

$jpgFolder = 'high_uploads/jpg_files/';
$pdfFolder = 'high_uploads/pdf_files/';
$uploadedImage = $_GET['image'] ?? '';

if (!$uploadedImage) die("No image specified.");

$baseName = pathinfo($uploadedImage, PATHINFO_FILENAME);
$pdfFile = $pdfFolder . $baseName . '.pdf';

if (!file_exists($pdfFile)) {
    $pngFiles = glob($jpgFolder . $baseName . '*.png');
    if (!$pngFiles) die("No PNGs found to generate PDF.");

    $pdf = new FPDF();
    foreach ($pngFiles as $png) {
        list($w, $h) = getimagesize($png);
        $pdf->AddPage('P', [$w * 0.75, $h * 0.75]); // scale to points
        $pdf->Image($png, 0, 0, $w * 0.75, $h * 0.75);
    }

    if (!is_dir($pdfFolder)) mkdir($pdfFolder, 0777, true);
    $pdf->Output('F', $pdfFile);
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($pdfFile) . '"');
readfile($pdfFile);
exit;
