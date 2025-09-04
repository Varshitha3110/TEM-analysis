<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Exported PDFs — Nanoparticle Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="style.css" />


  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>


  <style>
    body { font-family: 'Poppins', sans-serif; }

    .pdf-title {
      cursor: pointer;
      display: inline-block;
      padding: 8px 14px;
      background: #f8fafc;
      border-radius: 8px;
      color: #0d6efd;
      font-weight: 600;
      margin-right: 12px;
      transition: background 0.25s, transform 0.25s;
      text-decoration: none;
    }
    .pdf-title:hover {
      background: #e9f2ff;
      transform: translateY(-2px);
    }

    .delete-btn {
      cursor: pointer;
      color: #fff;
      background: #dc3545;
      border: none;
      padding: 4px 8px;
      border-radius: 6px;
      margin-left: 12px;
      font-size: 13px;
      transition: background 0.2s;
    }
    .delete-btn:hover { background: #b02a37; }

    .download-btn {
      cursor: pointer;
      color: #fff;
      background: #198754;
      border: none;
      padding: 4px 8px;
      border-radius: 6px;
      margin-left: 8px;
      font-size: 13px;
      transition: background 0.2s;
    }
    .download-btn:hover { background: #146c43; }

    .preview-pdf {
      width: 95%;
      height: 750px;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin: 15px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      display: block; /* visible initially */
    }

    .open-link {
      display: none;
      margin: 10px 0;
      font-size: 14px;
      font-weight: 500;
      color: #0d6efd;
    }

    .excel-table {
      display: none;
      margin-top: 12px;
      border-collapse: collapse;
      width: 100%;
      border: 1px solid #ccc;
    }
    .excel-table th, .excel-table td {
      border-bottom: 1px solid #ccc;
      padding: 6px 8px;
    }
    .excel-table th {
      background: #f0f0f0;
      font-weight: 600;
    }
  </style>
</head>

<body>
<div class="app">

  <?php include 'sidebar_high.php'; ?>
  <main class="main">
    <?php include 'header_high.php'; ?>

    <section class="card" style="margin-top: 15px;">
      <h3>Exported PDFs</h3>
      
      <div id="pdfContainer" style="max-height:600px; overflow:auto; border:1px solid #ccc; padding:10px; border-radius:8px;"></div>
      <button id="downloadAllBtn" class="cta" style="margin-top:20px;">Download All</button>
    </section>
    

<script>
  
const pdfContainer = document.getElementById('pdfContainer');

// List PDFs
const pdfFiles = <?php
  $pdfs = array_filter(scandir("high_uploads/jpg_files/"), fn($f)=>!in_array($f, [".",".."]));
  echo json_encode(array_values($pdfs));
?>;


// List Excels
const excelFiles = <?php
  $excels = array_filter(scandir("high_uploads/excel_files/"), fn($f)=>!in_array($f, [".",".."]));
  echo json_encode(array_values($excels));
?>;

// Match Excel by splitting PDF name
async function findExcelByPdfName(pdfName) {
  const excelFolder = 'high_uploads/excel_files/';
  const cleanPdf = pdfName.replace(/[^a-z0-9]/gi, "").toLowerCase();

  for (const f of excelFiles) {
    const base = f.replace(/\.(xlsx|csv)$/i, "");
    const cleanExcel = base.replace(/[^a-z0-9]/gi, "").toLowerCase();
    if (cleanExcel.includes(cleanPdf) || cleanPdf.includes(cleanExcel)) {
      return excelFolder + f;
    }
  }
  return null;
}


// Render PDF + Excel
function renderPdf(file){
  const fileWrapper = document.createElement('div');
  fileWrapper.dataset.filename = file;  // <-- important
fileWrapper.classList.add('card');

  fileWrapper.style.marginBottom = '30px';
  const baseName = file.replace(".pdf","");

  // Title
  const title = document.createElement('span');
  title.textContent = file;
  title.classList.add('pdf-title');

  // Delete button
// Delete button
const delBtn = document.createElement('button');
  delBtn.innerHTML = '<i class="fas fa-trash" style="margin-right:6px;"></i> Delete';

delBtn.textContent = "Delete";
delBtn.classList.add('delete-btn');
delBtn.onclick = () => {
    if(confirm(`Delete ${file} and move to trash?`)){
        fetch(`delete_to_trash.php?file=${encodeURIComponent(file)}`)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success'){
                alert(data.message);
                pdfContainer.removeChild(fileWrapper);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while deleting the file.');
        });
    }
};


  // Download button
  const downloadBtn = document.createElement('button');
    downloadBtn.innerHTML = '<i class="fas fa-download" style="margin-right:6px;"></i> Download';

  downloadBtn.textContent = "Download";
  downloadBtn.classList.add('download-btn');
  downloadBtn.onclick = () => {
    const a = document.createElement('a');
    a.href = 'high_uploads/jpg_files/' + file;
    a.download = file;
    a.click();
  };

  fileWrapper.appendChild(title);
  fileWrapper.appendChild(delBtn);
  fileWrapper.appendChild(downloadBtn);

  // Open in new tab link (hidden initially)
  const openLink = document.createElement('a');
  openLink.href = 'high_uploads/jpg_files/' + file;
  openLink.target = '_blank';
  openLink.classList.add('open-link');
  openLink.textContent = "🔗 Open PDF in New Tab";

  // PDF preview (hidden initially)
  const pdfIframe = document.createElement('iframe');
  pdfIframe.src = 'high_uploads/jpg_files/' + file;
  pdfIframe.classList.add('preview-pdf');

  fileWrapper.appendChild(openLink);
  fileWrapper.appendChild(pdfIframe);

  // Excel container (hidden initially)
  const excelTable = document.createElement('table');
  excelTable.classList.add('excel-table');
  fileWrapper.appendChild(excelTable);

  // Toggle everything on title click
  title.addEventListener('click', () => {
    const visible = pdfIframe.style.display === 'block';
    if(!visible){
      openLink.style.display = 'block';
      pdfIframe.style.display = 'block';

      findExcelByPdfName(baseName).then(excelPath => {
        if(excelPath){
          fetch(excelPath)
            .then(res => res.arrayBuffer())
            .then(ab => {
              const wb = XLSX.read(ab, { type:'array' });
              const sheet = wb.Sheets[wb.SheetNames[0]];
              const data = XLSX.utils.sheet_to_json(sheet);
              if(data.length > 0){
                excelTable.innerHTML = "";
                const thead = document.createElement('thead');
                const trh = document.createElement('tr');
                Object.keys(data[0]).forEach(k=>{
                  const th = document.createElement('th');
                  th.textContent = k;
                  trh.appendChild(th);
                });
                thead.appendChild(trh);
                excelTable.appendChild(thead);

                const tbody = document.createElement('tbody');
                data.forEach(row=>{
                  const tr = document.createElement('tr');
                  Object.values(row).forEach(val=>{
                    const td = document.createElement('td');
                    td.textContent = val;
                    tr.appendChild(td);
                  });
                  tbody.appendChild(tr);
                });
                excelTable.appendChild(tbody);
              }
            });
        }
      });
      excelTable.style.display = 'table';
      excelTable.scrollIntoView({behavior: "smooth"});
    } else {
      openLink.style.display = 'none';
      pdfIframe.style.display = 'none';
      excelTable.style.display = 'none';
    }
  });

  pdfContainer.appendChild(fileWrapper);
}

// Render all PDFs
pdfFiles.forEach(f => renderPdf(f));

// Download All PDFs
document.getElementById('downloadAllBtn').addEventListener('click', async () => {
  const zip = new JSZip();
  const folder = zip.folder("PDFs");

  for (const f of pdfFiles) {
    const response = await fetch('high_uploads/jpg_files/' + f);
    const blob = await response.blob();
    folder.file(f, blob);
  }

  zip.generateAsync({ type: "blob" }).then(content => {
    saveAs(content, "All_PDFs.zip");
  });
});

</script>

  </main>
</div>
</body>
</html>
