<?php
// high_images_uploaded.php

// ====== CONFIG ======
$uploadFolder = 'high_uploads/';
$jpgFolder    = 'high_uploads/jpg_files/';
$excelFolder  = 'high_uploads/excel_files/';
$historyFile  = __DIR__ . '/high_history.json';

// Load existing history
$history = file_exists($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];

// Sort newest first
usort($history, function($a, $b){
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Uploaded Images — Nanoparticle Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="stylesheet" href="style.css" />
<style>
table { width: 100%; border-collapse: collapse; background: #0b1220; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.3); font-family: 'Poppins', sans-serif; }
th, td { padding: 12px 15px; border-bottom: 1px solid #1a1f2b; text-align: left; color: #fff; }
th { background-color: #007bff; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
tr:hover { background-color: #1e2636; }
td a { color: #00d1ff; text-decoration: none; cursor: pointer; }
td a:hover { text-decoration: underline; }
button.delete-btn { padding: 6px 14px; background-color: #dc3545; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background-color 0.2s, transform 0.2s; }
button.delete-btn:hover { background-color: #b52a37; transform: scale(1.05); }
@media screen and (max-width: 768px) { table, th, td { display: block; } th { position: sticky; top: 0; background: #007bff; } td { border-bottom: 1px solid #1a1f2b; padding: 10px; } }
</style>
</head>
<body>

<div class="app">
  <?php include 'sidebar_high.php'; ?>
  <main class="main">
    <?php include 'header_high.php'; ?>

    <section class="card">
      <h2>Uploaded Images history</h2>

<?php if (empty($history)): ?>
    <p>No uploads yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Image Name</th>
                <th>CSV File</th>
                <th>PNG Files</th>
                <th>Upload Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $index => $item): ?>
            <tr>
                <td><?= htmlspecialchars(basename($item['image'])) ?></td>
                <td>
                    <?php if(!empty($item['csv'])): ?>
                        <a href="<?= $item['csv'] ?>" download>Download CSV</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($item['pngs'])): ?>
                        <a href="#" class="download-pngs" data-pngs='<?= json_encode($item['pngs']) ?>'>Download PNGs</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= $item['date'] ?></td>
                <td>
                    <form onsubmit="deleteItem(<?= $index ?>); return false;">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
// Download all PNGs
document.querySelectorAll('.download-pngs').forEach(el => {
    el.addEventListener('click', e => {
        e.preventDefault();
        const pngFiles = JSON.parse(el.getAttribute('data-pngs'));
        pngFiles.forEach(file => {
            const a = document.createElement('a');
            a.href = file;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    });
});

// Delete item from history
function deleteItem(index){
    if(!confirm("Are you sure you want to delete this entry?")) return;

    fetch('delete_to_trash.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'index=' + index
    }).then(res => res.json())
      .then(data => {
        alert(data.message);
        if(data.status === 'success') location.reload();
      });
}
</script>

    </section>
  </main>
</div>

</body>
</html>
