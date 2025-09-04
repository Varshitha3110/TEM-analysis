<?php
// high_images_uploaded.php

// ====== CONFIG ======
$uploadFolder = 'high_uploads/';
$jpgFolder    = 'high_uploads/jpg_files/';
$excelFolder  = 'high_uploads/excel_files/';
$historyFile  = __DIR__ . '/high_history.json';

// Load existing history
$history = file_exists($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];

// Get uploaded images
$images = array_filter(scandir($uploadFolder) ?: [], fn($f) => !in_array($f, [".", ".."]) && preg_match("/\.(jpg|jpeg|png|gif)$/i", $f));

// Sort newest first
usort($images, function($a, $b) use ($uploadFolder) {
    return filemtime($uploadFolder . $b) - filemtime($uploadFolder . $a);
});

// Update history.json
foreach ($images as $img) {
    $imagePath = $uploadFolder . $img;
    $baseName = pathinfo($img, PATHINFO_FILENAME);

    // Skip if already in history
    $exists = false;
    foreach ($history as $h) {
        if (isset($h['image']) && $h['image'] === $imagePath) { $exists = true; break; }
    }
    if ($exists) continue;

// Find CSV
$csvMatches = glob($excelFolder . $baseName . '*.csv');
$csvFile = !empty($csvMatches) ? 'high_uploads/excel_files/' . basename($csvMatches[0]) : '';

// Find all generated PNGs
$pngs = glob($jpgFolder . $baseName . '*.png');
$pngs = array_map(fn($f) => 'high_uploads/jpg_files/' . basename($f), $pngs);


    // Add entry to history
    $history[] = [
        'image' => $imagePath,
        'csv'   => $csvFile,
        'pngs'  => $pngs,
        'date'  => date('Y-m-d H:i:s', filemtime($imagePath))
    ];
}

// Save updated history
file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Images Uploaded — Nanoparticle Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="stylesheet" href="style.css" />

<style>
.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 15px;
  margin-top: 20px;
}
.image-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  padding: 10px;
  text-align: center;
}
.image-card img {
  width: 100%;
  height: 500px;
  object-fit: cover;
  border-radius: 8px;
}
.image-name {
  margin-top: 8px;
  font-size: 14px;
  color: #007bff;
  cursor: pointer;
  text-align: center;
}
.image-name:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="app">
  <?php include 'sidebar_high.php'; ?>
  <main class="main">
    <?php include 'header_high.php'; ?>
    <section class="card">
      <h2>Uploaded Images</h2>

<div class="image-grid">
<?php foreach ($history as $item): ?>
    <div class="image-card">
        <img src="<?= htmlspecialchars($item['image']) ?>" alt="Uploaded Image">
        <p class="image-name" 
           data-pngs='<?= isset($item['pngs']) ? json_encode($item['pngs']) : '[]' ?>'
           data-csv='<?= isset($item['csv']) ? htmlspecialchars($item['csv']) : '' ?>'>
           <?= htmlspecialchars(basename($item['image'])) ?>
        </p>
    </div>
<?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.image-name').forEach(el => {
    el.addEventListener('click', () => {
        const pngFiles = JSON.parse(el.getAttribute('data-pngs'));
        const csvFile  = el.getAttribute('data-csv');

        // Download all PNGs
        pngFiles.forEach(file => {
            if(file) {
                const a = document.createElement('a');
                a.href = file;
                a.download = '';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        });

        // Download CSV
        if(csvFile) {
            const a = document.createElement('a');
            a.href = csvFile;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    });
});
</script>

    </section>
  </main>
</div>

</body>
</html> 