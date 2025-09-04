<?php
// history.php
$historyFile = __DIR__ . '/history.json';
$history = file_exists($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];

// Handle permanent delete
if (isset($_POST['delete'])) {
    $delIndex = intval($_POST['delete_index']);
    if (isset($history[$delIndex])) {

        // Delete actual files
        $item = $history[$delIndex];
        $types = ['image','pdf','excel'];
        foreach ($types as $type) {
            if (!empty($item[$type])) {
                deleteFile($type, $item[$type]);
            }
        }

        // Remove from history JSON
        array_splice($history, $delIndex, 1);
        file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT));
        echo "<script>window.location.href='history.php';</script>";
        exit;
    }
}

// Function to delete file from main or trash folder
// Function to delete file ONLY from trash folder
function deleteFile($type, $fileName){
    $fileName = basename($fileName);
    $folders = [
        'image' => 'uploads/trash/',
        'pdf'   => 'uploads/trash/',
        'excel' => 'uploads/trash/'
    ];

    if(!isset($folders[$type])) return;

    $path = $folders[$type] . $fileName;
    if(file_exists($path)){
        @unlink($path); // delete only from trash
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload History — Nanoparticle Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="style.css" />
<style>
table { width: 100%; border-collapse: collapse; background: #0b1220; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.3); font-family: 'Poppins', sans-serif; }
th, td { padding: 12px 15px; border-bottom: 1px solid #1a1f2b; text-align: left; color: #fff; cursor: pointer; }
th { background-color: #007bff; color: #fff; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
tr:hover { background-color: #1e2636; }
td a { color: #00d1ff; text-decoration: none; }
td a:hover { text-decoration: underline; }
button.delete-btn { padding: 6px 14px; background-color: #dc3545; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background-color 0.2s, transform 0.2s; }
button.delete-btn:hover { background-color: #b52a37; transform: scale(1.05); }
@media screen and (max-width: 768px) { table, th, td { display: block; } th { position: sticky; top: 0; background: #007bff; } td { border-bottom: 1px solid #1a1f2b; padding: 10px; } }
</style>
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <main class="main">
    <?php include 'header.php'; ?>
    <section class="card" style="margin-top: 15px;">
      <h2>Uploaded Images History</h2>

<?php if (count($history) === 0): ?>
    <p>No uploads yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Image Name</th>
                <th>Excel File</th>
                <th>PDF File</th>
                <th>Upload Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $index => $item): ?>
                <tr>
                    <td class="file-link" data-type="image" data-file="<?= htmlspecialchars($item['image']) ?>"><?= htmlspecialchars($item['image']) ?></td>
                    <td class="file-link" data-type="excel" data-file="<?= htmlspecialchars($item['excel']) ?>"><?= $item['excel'] ? htmlspecialchars($item['excel']) : '—' ?></td>
                    <td class="file-link" data-type="pdf" data-file="<?= htmlspecialchars($item['pdf']) ?>"><?= $item['pdf'] ? htmlspecialchars($item['pdf']) : '—' ?></td>
                    <td><?= $item['date'] ?></td>
                    <td>
<form onsubmit="permanentlyDelete('<?= $item['image'] ?>', this.querySelector('button')); return false;">
    <button type="submit" class="delete-btn">Permanently Delete</button>
</form>


                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
// Handle clicks on table cells
document.querySelectorAll('.file-link').forEach(td => {
    td.addEventListener('click', () => {
        const type = td.getAttribute('data-type');
        const fileName = td.getAttribute('data-file');
        if (!fileName) return;
if(type === 'excel') {
    if (!fileName) return; // skip if no Excel file
    const a = document.createElement('a');
    a.href = 'download.php?file=' + encodeURIComponent(fileName);
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    return;
}


        // For PDFs and Images, check main folder first
        let folder = (type === 'pdf') ? 'uploads/pdf_files/' : 'uploads/';
        let url = folder + fileName;

        fetch(url, { method: 'HEAD' }).then(res => {
            if(res.ok){
                window.open(url, '_blank');
            } else {
                // Try trash folder
                let trashUrl = 'uploads/trash/' + fileName;
                fetch(trashUrl, { method: 'HEAD' }).then(r => {
                    if(r.ok) window.open(trashUrl, '_blank');
                    else alert('File not found in uploads or trash: ' + fileName);
                });
            }
        });
    });
});
function permanentlyDelete(fileName, btn) {
    if (!confirm(`Are you sure you want to permanently delete ${fileName}?`)) return;

    fetch("delete_trash.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "file=" + encodeURIComponent(fileName)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success" || data.status === "partial") {
            // Remove the row from the table
            const row = btn.closest('tr');
            if (row) row.remove();
        }
    });
}



</script>
<script>
function confirmDelete(form) {
    return confirm("Are you sure you want to permanently delete this file?");
}
</script>

    </section>
  </main>
</div>
</body>
</html>