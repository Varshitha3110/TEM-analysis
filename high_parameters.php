<?php
// high_parameters.php
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Parameters — Nanoparticle Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link href="style.css" rel="stylesheet">


<style>
body { font-family:'Poppins',sans-serif; }
.excel-title { cursor:pointer; display:inline-block; padding:8px 14px; background:#f8fafc; border-radius:8px; color:#0d6efd; font-weight:600; margin-right:12px; text-decoration:none; }
.excel-title:hover { background:#e9f2ff; transform:translateY(-2px); }
.delete-btn, .download-btn { cursor:pointer; color:#fff; border:none; padding:4px 8px; border-radius:6px; margin-left:12px; font-size:13px; }
.delete-btn { background:#dc3545; } .delete-btn:hover { background:#b02a37; }
.download-btn { background:#198754; } .download-btn:hover { background:#145c32; }
table { border-collapse: collapse; width:100%; margin-top:10px; }
th, td { padding:6px 8px; border-bottom:1px solid #ccc; }
th { background:#f4f2f2; border-bottom:5px solid #f9f9f9; color:"#0c0c0cff"; }
.card { border:1px solid #0a0a0aff; padding:12px; border-radius:8px; margin-bottom:20px; }
</style>
</head>
<body>

<div class="app">
<?php include 'sidebar_high.php'; ?>
<main class="main">
<?php include 'header_high.php'; ?>

<section class="card" style="margin-top:15px;">
    <h3>Parameter Excel Files</h3>
    <div id="paramTableContainer" style="max-height:600px; overflow:auto;"></div>
    <button id="downloadAllParams" class="cta" style="margin-top:20px;">Download All Parameters</button>
</section>

<script>
const paramContainer = document.getElementById('paramTableContainer');

// --- Load all parameter files ---
let paramFiles = [<?php
$folder = "high_uploads/parameters_files/";
$filesWithTime = [];
if(is_dir($folder)){
    $allFiles = scandir($folder);
    foreach($allFiles as $f){
        if($f!="." && $f!=".." && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ["xlsx","xls"])){
            $filesWithTime[]=['name'=>$f,'time'=>filemtime($folder.$f)];
        }
    }
    usort($filesWithTime, fn($a,$b)=>$b['time']-$a['time']);
    $files = array_map(fn($f)=>$f['name'],$filesWithTime);
    echo '"' . implode('","',$files) . '"';
}
?>];

async function renderParamExcel(file){
    if(!file) return;
    const fileWrapper = document.createElement('div');
    fileWrapper.classList.add('card');

    // Header: file name + download + delete
    const headerDiv = document.createElement('div');
    headerDiv.style.display='flex';
    headerDiv.style.alignItems='center';
    headerDiv.style.gap='10px';
    const title = document.createElement('span');
    title.textContent = file;
    title.classList.add('excel-title');

    const downloadBtn = document.createElement('button');
    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
    downloadBtn.classList.add('download-btn');
    downloadBtn.onclick = ()=> {
        const a = document.createElement('a');
        a.href = 'high_uploads/parameters_files/' + encodeURIComponent(file);
        a.download = file;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    const delBtn = document.createElement('button');
    delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    delBtn.classList.add('delete-btn');
delBtn.onclick = ()=>{
    if(confirm(`Delete ${file}?`)){
        fetch('high_delete_to_trash.php?file=' + encodeURIComponent(file) + '&type=param')
        .then(res=>res.json())
        .then(resData=>{
            if(resData.status==='success') fileWrapper.remove();
            else alert(resData.message);
        });
    }
};


    headerDiv.appendChild(title);
    headerDiv.appendChild(downloadBtn);
    headerDiv.appendChild(delBtn);
    fileWrapper.appendChild(headerDiv);

    // Table
    const tableDiv = document.createElement('div');
    tableDiv.style.marginTop='10px';

    // Load Excel
    fetch('high_uploads/parameters_files/' + encodeURIComponent(file))
    .then(res=>res.arrayBuffer())
    .then(ab=>{
        const wb = XLSX.read(ab,{type:'array'});
        const sheet = wb.Sheets[wb.SheetNames[0]];
        const data = XLSX.utils.sheet_to_json(sheet);
        if(!data.length){ tableDiv.innerHTML='<p>No data</p>'; return; }

        const table = document.createElement('table');
        const thead = document.createElement('thead');
        const trh = document.createElement('tr');
        Object.keys(data[0]).forEach(k=>{
            const th = document.createElement('th'); th.textContent=k; trh.appendChild(th);
        });
        thead.appendChild(trh);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        data.forEach(row=>{
            const tr = document.createElement('tr');
            Object.values(row).forEach(val=>{
                const td = document.createElement('td'); td.textContent=val; tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        tableDiv.appendChild(table);
    })
    .catch(err=>{ tableDiv.innerHTML='<p>Error loading Excel</p>'; });

    fileWrapper.appendChild(tableDiv);
    paramContainer.appendChild(fileWrapper);

    // Inside your renderParamExcel(file) function, after creating the headerDiv

// Add double-click event on the title to open corresponding CSV
// Double-click to display corresponding CSV below the Excel table
title.ondblclick = () => {
    const baseName = file.split('.')[0];
    const csvPath = `high_uploads/excel_files/${encodeURIComponent(baseName)}.csv`;

    // Check if CSV exists
    fetch(csvPath)
    .then(res => {
        if(!res.ok) throw new Error("CSV not found");
        return res.text();
    })
    .then(csvText => {
        // Remove previous CSV table for this file if exists
        const existingCSV = fileWrapper.querySelector('.csv-table');
        if(existingCSV) existingCSV.remove();

        const rows = csvText.split("\n").filter(r => r.trim() !== "");
        if(rows.length === 0) return;

        const table = document.createElement('table');
        table.classList.add('csv-table');
        table.style.width = '100%';
        table.style.marginTop = '10px';
        table.style.borderCollapse = 'collapse';

        // Table header
        const thead = document.createElement('thead');
        const trh = document.createElement('tr');
        rows[0].split(",").forEach(h => {
            const th = document.createElement('th');
            th.textContent = h.trim();
            th.style.border = "1px solid #ccc";
            th.style.padding = "5px";
            th.style.background = "#f4f4f4";
            th.style.color="#0c0c0cff";
            trh.appendChild(th);
        });
        thead.appendChild(trh);
        table.appendChild(thead);

        // Table body
        const tbody = document.createElement('tbody');
        rows.slice(1).forEach(r => {
            const tr = document.createElement('tr');
            r.split(",").forEach(c => {
                const td = document.createElement('td');
                td.textContent = c.trim();
                td.style.border = "1px solid #ccc";
                td.style.padding = "5px";
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);

        // Append CSV table below the Excel table
        tableDiv.appendChild(table);
    })
    .catch(err => alert(`CSV file not found for ${baseName}`));
};

}

// Render all parameter files
paramFiles.forEach(f=>renderParamExcel(f));

// Download all parameters combined
document.getElementById('downloadAllParams').addEventListener('click', ()=>{
    const wb = XLSX.utils.book_new();
    const fetchPromises = paramFiles.map(f=>{
        return fetch('high_uploads/parameters_files/' + encodeURIComponent(f))
        .then(res=>res.arrayBuffer())
        .then(ab=>{
            const tempWb = XLSX.read(ab,{type:'array'});
            const ws = tempWb.Sheets[tempWb.SheetNames[0]];
            XLSX.utils.book_append_sheet(wb, ws, f.split('.')[0].substring(0,30));
        });
    });
    Promise.all(fetchPromises).then(()=> XLSX.writeFile(wb,'all_parameters_combined.xlsx'));
});
</script>

</main>
</div>
</body>
</html>
