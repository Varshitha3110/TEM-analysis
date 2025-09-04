<?php
// images_uploaded.php
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Images Uploaded — Nanoparticle Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="style.css" />

<style>
/* Image Grid */
.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

/* Image Card */
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

/* Date heading */
.date-heading {
  font-size: 18px;
  font-weight: 600;
  margin: 20px 0 10px;
  color: #fdfafaff;
}

/* Image name container */
.image-name {
  margin-top: 8px;
  font-size: 14px;
  color: #007bff;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
}
.image-name:hover {
  text-decoration: underline;
}
</style>
</head>
<body>

<div class="app">
  <?php include 'sidebar.php'; ?>
  <main class="main">
    <?php include 'header.php'; ?>

    <section class="card">
      <h2>Uploaded Images</h2>

<?php
$imageFolder = 'uploads/';
$images = array_filter(scandir($imageFolder), fn($f) => !in_array($f, [".", ".."]) && preg_match("/\.(jpg|jpeg|png|gif)$/i", $f));

// Sort by newest first
usort($images, function($a, $b) use ($imageFolder) {
    return filemtime($imageFolder . $b) - filemtime($imageFolder . $a);
});

// ===== HISTORY LOGGING =====
$historyFile = __DIR__ . '/history.json';
$history = file_exists($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];

foreach ($images as $img) {
    $excelFile = pathinfo($img, PATHINFO_FILENAME) . '.xlsx';
    $pdfFile = pathinfo($img, PATHINFO_FILENAME) . '.pdf';
    $date = date("Y-m-d H:i:s", filemtime($imageFolder . $img));

    // Check if already in history
    $exists = false;
    foreach ($history as $h) {
        if ($h['image'] === $img) { $exists = true; break; }
    }

    if (!$exists) {
        $history[] = [
            'image' => $img,
            'excel' => $excelFile,
            'pdf' => $pdfFile,
            'date' => $date
        ];
    }
}

// Save updated history
file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT));
// ===== END HISTORY LOGGING =====

if (count($images) === 0) {
    echo "<p>No images uploaded yet.</p>";
} else {
    $grouped = [];
    foreach ($images as $img) {
        $date = date("d M Y", filemtime($imageFolder . $img));
        $grouped[$date][] = $img;
    }

    foreach ($grouped as $date => $imgs) {
        echo "<div class='date-heading'>$date</div>";
        echo "<div class='image-grid'>";
        foreach ($imgs as $img) {
            $pdfFileUrl = 'uploads/pdf_files/' . pathinfo($img, PATHINFO_FILENAME) . '.pdf';
            $excelFileUrl = 'download.php?file=' . urlencode(pathinfo($img, PATHINFO_FILENAME) . '.xlsx');

            echo '<div class="image-card">';
            echo '<img src="'.$imageFolder.$img.'" alt="Uploaded Image">';
            echo '<p class="image-name" data-pdf="'.$pdfFileUrl.'" data-excel="'.$excelFileUrl.'">'.htmlspecialchars($img).'</p>';
            echo '</div>';
        }
        echo "</div>";
    }
}
?>

<script>
// Click on image name: open PDF & download Excel
document.querySelectorAll('.image-name').forEach(el => {
    el.addEventListener('click', () => {
        const pdfFile = el.getAttribute('data-pdf');
        const excelFile = el.getAttribute('data-excel');

        // Open PDF in new tab if exists
        if (pdfFile && pdfFile !== '') {
            window.open(pdfFile, '_blank');
        }

        // Trigger Excel download if exists
        if (excelFile && excelFile !== '') {
            const a = document.createElement('a');
            a.href = excelFile;
            a.download = ''; // let download.php handle filename
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
