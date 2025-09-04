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
    body {
      font-family: 'Poppins', sans-serif;
    }

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

    .delete-btn:hover {
      background: #b02a37;
    }

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

    .download-btn:hover {
      background: #146c43;
    }

    .preview-pdf {
      width: 95%;
      height: 750px;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin: 15px 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      display: block;
      /* visible initially */
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

    .excel-table th,
    .excel-table td {
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

    <?php include 'sidebar.php'; ?>
    <main class="main">
      <?php include 'header.php'; ?>

      <section class="card" style="margin-top: 15px;">
        <h3>Exported PDFs</h3>

        <div id="pdfContainer" style="max-height:600px; overflow:auto; border:1px solid #ccc; padding:10px; border-radius:8px;"></div>
        <button id="downloadAllBtn" class="cta" style="margin-top:20px;">Download All</button>
      </section>


<script>
  const pdfContainer = document.getElementById('pdfContainer');

  // List PDFs
  const pdfFiles = <?php
    $pdfs = array_filter(scandir("uploads/pdf_files/"), function ($f) {
      return $f !== "." && $f !== ".." && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === "pdf";
    });
    echo json_encode(array_values($pdfs));
  ?>;

  // List Excels
  const excelFiles = <?php
    $excels = array_filter(scandir("uploads/excel_files/"), fn($f) => !in_array($f, [".", ".."]));
    echo json_encode(array_values($excels));
  ?>;

  // Match Excel by splitting PDF name
async function findExcelByPdfName(pdfName) {
  const excelFolder = 'uploads/excel_files/';
  const cleanPdf = pdfName.replace(/\.[^/.]+$/, ""); // remove extension

  for (const f of excelFiles) {
    const base = f.replace(/\.[^/.]+$/, ""); // remove Excel extension
    // Compare base names ignoring case and non-alphanumeric chars
    if (base.replace(/[^a-z0-9]/gi, '').toLowerCase() === cleanPdf.replace(/[^a-z0-9]/gi, '').toLowerCase()) {
      return excelFolder + f; // return the full path
    }
  }
  return null; // no match found
}


  // Load config.json first
  let pdfConfig = {};
  fetch('uploads/config.json')
    .then(res => res.json())
    .then(data => {
      pdfConfig = data;
      // Now render PDFs
// Sort PDFs by modification time (newest first)
async function sortFilesByModifiedTime(files, folderPath) {
  const filesWithTime = await Promise.all(files.map(async f => {
    const response = await fetch(folderPath + f, { method: 'HEAD' });
    const lastModified = response.headers.get('last-modified');
    return {
      name: f,
      time: lastModified ? new Date(lastModified).getTime() : 0
    };
  }));
  filesWithTime.sort((a, b) => b.time - a.time); // newest first
  return filesWithTime.map(f => f.name);
}

// Sort then render
sortFilesByModifiedTime(pdfFiles, 'uploads/pdf_files/').then(sortedFiles => {
  sortedFiles.forEach(f => renderPdf(f));
});
    })
    .catch(err => console.error('Error loading config.json', err));

  function renderPdf(file) {
    const fileWrapper = document.createElement('div');
    fileWrapper.dataset.filename = file;
    fileWrapper.classList.add('card');
    fileWrapper.style.marginBottom = '30px';
    const baseName = file.replace(".pdf", "");

    // Title
    const title = document.createElement('span');
    title.textContent = file;
    title.classList.add('pdf-title');

    // --- Config display below title ---
// --- Config display below title ---
const configP = document.createElement('p');
configP.style.marginTop = '5px';
configP.style.fontWeight = '500';

// Get the last modified date of the PDF
async function getFileLastModified(filePath) {
  try {
    const res = await fetch(filePath, { method: 'HEAD' });
    const lastModified = res.headers.get('last-modified');
    if (lastModified) return new Date(lastModified).toLocaleString();
    return 'Unknown date';
  } catch (err) {
    console.error(err);
    return 'Unknown date';
  }
}

// Set content
getFileLastModified('uploads/pdf_files/' + file).then(dateStr => {
  if (pdfConfig && pdfConfig.scale !== undefined && pdfConfig.thresholds) {
    const scale = pdfConfig.scale;
    const thresholds = pdfConfig.thresholds.join(', ');
    configP.textContent = `Scale: ${scale} | Thresholds: ${thresholds} | Date: ${dateStr}`;
  } else {
    configP.textContent = 'No scale/threshold info available';
  }
});
    configP.style.marginTop = '5px';
    configP.style.fontWeight = '500';
    if (pdfConfig && pdfConfig.scale !== undefined && pdfConfig.thresholds) {
      const scale = pdfConfig.scale;
      const thresholds = pdfConfig.thresholds.join(', ');
      configP.textContent = `Scale: ${scale} | Thresholds: ${thresholds}`;
    } else {
      configP.textContent = 'No scale/threshold info available';
    }

    // Delete button
    const delBtn = document.createElement('button');
    delBtn.innerHTML = '<i class="fas fa-trash" style="margin-right:6px;"></i> Delete';
    delBtn.classList.add('delete-btn');
    delBtn.onclick = () => {
      if (confirm(`Delete ${file} and move to trash?`)) {
        fetch(`delete_to_trash.php?file=${encodeURIComponent(file)}`)
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') pdfContainer.removeChild(fileWrapper);
            else alert('Error: ' + data.message);
          })
          .catch(err => { console.error(err); alert('An error occurred'); });
      }
    };

    // Download button
    const downloadBtn = document.createElement('button');
    downloadBtn.innerHTML = '<i class="fas fa-download" style="margin-right:6px;"></i> Download';
    downloadBtn.classList.add('download-btn');
    downloadBtn.onclick = () => {
      const a = document.createElement('a');
      a.href = 'uploads/pdf_files/' + file;
      a.download = file;
      a.click();
    };

// PDF preview
const pdfIframe = document.createElement('iframe');
pdfIframe.src = 'uploads/pdf_files/' + file;
pdfIframe.classList.add('preview-pdf');
pdfIframe.style.display = 'block'; // <-- make PDF visible initially

// Open in new tab link
const openLink = document.createElement('a');
openLink.href = 'uploads/pdf_files/' + file;
openLink.target = '_blank';
openLink.classList.add('open-link');
openLink.textContent = "🔗 Open PDF in New Tab";
openLink.style.display = 'inline-block'; // <-- show link initially


    // Excel table
    const excelTable = document.createElement('table');
    excelTable.classList.add('excel-table');
    excelTable.style.display = 'none';

    fileWrapper.appendChild(title);
    fileWrapper.appendChild(delBtn);
    fileWrapper.appendChild(downloadBtn);
    fileWrapper.appendChild(configP);
    fileWrapper.appendChild(openLink);
    fileWrapper.appendChild(pdfIframe);
    fileWrapper.appendChild(excelTable);
    pdfContainer.appendChild(fileWrapper);

    // Toggle display on title click
   title.addEventListener('click', async () => {
  const excelPath = await findExcelByPdfName(baseName);
  if (!excelPath) {
    alert("No matching CSV/Excel found for this PDF.");
    return;
  }

  // Toggle: if already visible, hide
  if (excelTable.style.display === 'table') {
    excelTable.style.display = 'none';
    return;
  }

  // Fetch Excel/CSV file
  fetch(excelPath)
    .then(res => res.arrayBuffer())
    .then(ab => {
      const wb = XLSX.read(ab, { type: 'array' });
      const sheet = wb.Sheets[wb.SheetNames[0]];
      const data = XLSX.utils.sheet_to_json(sheet);

      if (data.length > 0) {
        // Clear previous content
        excelTable.innerHTML = "";

        // Table header
        const thead = document.createElement('thead');
        const trh = document.createElement('tr');
        Object.keys(data[0]).forEach(k => {
          const th = document.createElement('th');
          th.textContent = k;
          trh.appendChild(th);
        });
        thead.appendChild(trh);
        excelTable.appendChild(thead);

        // Table body
        const tbody = document.createElement('tbody');
        data.forEach(row => {
          const tr = document.createElement('tr');
          Object.values(row).forEach(val => {
            const td = document.createElement('td');
            td.textContent = val;
            tr.appendChild(td);
          });
          tbody.appendChild(tr);
        });
        excelTable.appendChild(tbody);

        excelTable.style.display = 'table'; // Show the table
      } else {
        excelTable.innerHTML = "<tr><td>No data found in CSV</td></tr>";
        excelTable.style.display = 'table';
      }
    })
    .catch(err => {
      console.error(err);
      excelTable.innerHTML = "<tr><td>Error loading CSV</td></tr>";
      excelTable.style.display = 'table';
    });
});


  }

  // Download All PDFs
  document.getElementById('downloadAllBtn').addEventListener('click', async () => {
    const zip = new JSZip();
    const folder = zip.folder("PDFs");
    for (const f of pdfFiles) {
      const response = await fetch('uploads/pdf_files/' + f);
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