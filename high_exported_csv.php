<!doctype html>


<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Export CSV — Nanoparticle Dashboard</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- XLSX -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <link rel="stylesheet" href="style.css" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

    .excel-title {
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

    .excel-title:hover {
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

    .preview-pdf {
      width: 100%;
      height: 600px;
      border: 1px solid #ccc;
      border-radius: 10px;
      display: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .open-pdf-link {
      display: none;
      margin: 8px 0;
      color: #0d6efd;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }

    .open-pdf-link:hover {
      text-decoration: underline;
    }

    .flex-row {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      align-items: flex-start;
    }

    .flex-col {
      flex: 1;
    }

    table {
      border-collapse: collapse;
      width: 500px;
      border: 12px;
    }

    th,
    td {
      padding: 6px 8px;
      border-bottom: 1px solid #ccc;
    }

    th {
      background: #f4f2f2ff;
      border-bottom: 5px solid #f9f9f9ff;
      color: #020202ff;

    }
  </style>
</head>

<body>
  <div class="app">

    <?php include 'sidebar_high.php'; ?>
    <main class="main">
      <?php include 'header_high.php'; ?>

      <section class="card" style="margin-top: 15px;">
        <h3>Exported CSV Tables</h3>
        <div id="excelTableContainer" style="max-height:600px; overflow:auto; border:1px solid #ccc; padding:10px; border-radius:8px;"></div>
        <button id="downloadAllBtn" class="cta" style="margin-top:20px;">Download All</button>
      </section>

      <script>
        const excelContainer = document.getElementById('excelTableContainer');

        // Load Excel files (sorted by newest first)
        // PHP section inside <script>
let excelFiles = <?php
$folder = "high_uploads/excel_files/";
$filesWithTime = [];
if (is_dir($folder)) {
    $allFiles = scandir($folder);
    foreach ($allFiles as $f) {
        if ($f !== "." && $f !== ".."
            && in_array(pathinfo($f, PATHINFO_EXTENSION), ["csv"])
            && stripos($f, 'combined') === false) 
        {
            $filesWithTime[] = [
                'name' => $f,
                'time' => filemtime($folder . $f) // UNIX timestamp
            ];
        }
    }
    // Sort by filemtime descending
    usort($filesWithTime, fn($a, $b) => $b['time'] - $a['time']);
}
echo json_encode($filesWithTime);
?>;

        // List all PDFs
        const pdfFiles = <?php
                          $pdfs = array_filter(scandir("high_uploads/jpg_files/"), fn($f) => !in_array($f, [".", ".."]));
                          echo json_encode(array_values($pdfs));
                          ?>;

        async function findPdfByExcelName(excelName) {
          const pdfFolder = 'high_uploads/jpg_files/';
          const cleanExcel = excelName.replace(/\.(xlsx|xls|csv)$/i, "").replace(/[^a-z0-9]/gi, "").toLowerCase();

          for (const f of pdfFiles) {
            const cleanPdf = f.replace(/\.pdf$/i, "").replace(/[^a-z0-9]/gi, "").toLowerCase();
            if (cleanPdf.includes(cleanExcel) || cleanExcel.includes(cleanPdf)) {
              return pdfFolder + f;
            }
          }
          return null;
        }


        // Render Excel + PDF side by side
      async function renderExcel(fileObj) {
  if (!fileObj) return;

  const file = fileObj.name;
  const fileTime = new Date(fileObj.time * 1000); // convert UNIX timestamp to JS Date
  const formattedTime = fileTime.toLocaleString(); // local date+time

  const fileWrapper = document.createElement('div');
  fileWrapper.classList.add('card');
  fileWrapper.style.marginBottom = '40px';

  const baseName = file.split(".")[0];

  // --- Header ---
  const headerDiv = document.createElement('div');
  headerDiv.style.display = 'flex';
  headerDiv.style.alignItems = 'center';
  headerDiv.style.marginBottom = '10px';
const titleWrapper = document.createElement('div');
titleWrapper.style.display = 'flex';
titleWrapper.style.flexDirection = 'column';
titleWrapper.style.alignItems = 'flex-start';

const title = document.createElement('span');
title.textContent = file;
title.classList.add('excel-title');

const dateSpan = document.createElement('span');
dateSpan.textContent = formattedTime;
dateSpan.style.fontSize = "13px";
dateSpan.style.color = "#f4f7f6ff";
dateSpan.style.marginTop = "4px";
dateSpan.style.marginLeft = "2px";



          const delBtn = document.createElement('button');
          delBtn.innerHTML = '<i class="fas fa-trash" style="margin-right:6px;"></i> Delete';
          delBtn.classList.add('delete-btn');
          delBtn.onclick = () => {
            if (confirm(`Delete ${file}?`)) {
              fetch('delete_to_trash.php?file=' + file)
                .then(res => res.json())
                .then(resData => {
                  if (resData.status === 'success') fileWrapper.remove();
                  else alert('Failed to delete');
                });
            }
          };

          const downloadBtn = document.createElement('button');
          downloadBtn.innerHTML = '<i class="fas fa-download" style="margin-right:6px;"></i> Download';
          downloadBtn.classList.add('delete-btn');
          downloadBtn.style.background = "#198754";
          downloadBtn.style.marginLeft = "8px";
          downloadBtn.onclick = () => {
            const a = document.createElement('a');
            a.href = 'high_uploads/excel_files/' + file;
            a.download = file;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
          };


titleWrapper.appendChild(title);
titleWrapper.appendChild(dateSpan);
headerDiv.appendChild(titleWrapper);
          headerDiv.appendChild(delBtn);
          headerDiv.appendChild(downloadBtn);
          fileWrapper.appendChild(headerDiv);

          const contentDiv = document.createElement('div');
          contentDiv.style.display = 'flex';
          contentDiv.style.gap = '20px';

          const tableWrapper = document.createElement('div');
          tableWrapper.style.flex = '1';

          const imgAndPdfWrapper = document.createElement('div');
          imgAndPdfWrapper.style.display = 'flex';
          imgAndPdfWrapper.style.flexDirection = 'column';
          imgAndPdfWrapper.style.alignItems = 'center';
          imgAndPdfWrapper.style.flex = '0 0 400px';

          // Original Image
          const origImg = document.createElement('img');
          origImg.src = "high_uploads/" + baseName + ".jpg"; // adjust if extension is different
          origImg.style.width = '100%';
          origImg.style.height = 'auto';
          origImg.style.marginBottom = '10px';
          origImg.addEventListener('dblclick', () => window.open(origImg.src, '_blank'));
          imgAndPdfWrapper.appendChild(origImg);

          // PDF
          const genPdfIframe = document.createElement('iframe');
          genPdfIframe.style.width = '100%';
          genPdfIframe.style.height = '600px';
          genPdfIframe.style.border = '1px solid #ccc';
          genPdfIframe.style.borderRadius = '8px';
          genPdfIframe.style.display = 'none';
          imgAndPdfWrapper.appendChild(genPdfIframe);

          const openPdfLink = document.createElement('a');
          openPdfLink.target = '_blank';
          openPdfLink.textContent = 'Open images in new tab';
          openPdfLink.style.display = 'none';
          openPdfLink.style.color = "#fff";
          openPdfLink.style.background = "#0d0d0dff";
          openPdfLink.style.padding = "6px 12px";
          openPdfLink.style.marginTop = "10px";
          openPdfLink.style.borderRadius = "6px";
          openPdfLink.style.textDecoration = "none";
          imgAndPdfWrapper.appendChild(openPdfLink);

          // Fetch PDF path asynchronously
          const pdfPath = await findPdfByExcelName(baseName);
          if (pdfPath) {
            genPdfIframe.src = pdfPath;
            genPdfIframe.addEventListener('dblclick', () => window.open(pdfPath, '_blank'));
            openPdfLink.href = pdfPath;
            openPdfLink.style.display = 'inline-block';
          }

          // Checkbox options
          const optionsDiv = document.createElement('div');
          optionsDiv.style.marginBottom = '10px';
          optionsDiv.innerHTML = `
    <label><input type="checkbox" class="show-image-toggle" checked> Show Image</label><br>
    <label><input type="checkbox" class="show-pdf-toggle"> Show PDF</label>
  `;
          optionsDiv.querySelector('.show-image-toggle').addEventListener('change', e => {
            origImg.style.display = e.target.checked ? 'block' : 'none';
          });
          optionsDiv.querySelector('.show-pdf-toggle').addEventListener('change', e => {
            genPdfIframe.style.display = e.target.checked ? 'block' : 'none';
          });
          fileWrapper.appendChild(optionsDiv);

          contentDiv.appendChild(tableWrapper);
          contentDiv.appendChild(imgAndPdfWrapper);
          fileWrapper.appendChild(contentDiv);
          excelContainer.appendChild(fileWrapper);

          // Load Excel table
          fetch('high_uploads/excel_files/' + file)
            .then(res => res.arrayBuffer())
            .then(ab => {
              const workbook = XLSX.read(ab, {
                type: 'array'
              });
              const sheet = workbook.Sheets[workbook.SheetNames[0]];
              const data = XLSX.utils.sheet_to_json(sheet);
              if (!data.length) return;

              const table = document.createElement('table');
              const thead = document.createElement('thead');
              const trh = document.createElement('tr');
              Object.keys(data[0]).forEach(k => {
                const th = document.createElement('th');
                th.textContent = k;
                trh.appendChild(th);
              });
              thead.appendChild(trh);
              table.appendChild(thead);

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
              table.appendChild(tbody);
              tableWrapper.appendChild(table);
            })
            .catch(err => {
              tableWrapper.innerHTML = `<p>Error loading Excel: ${file}</p>`;
            });
        }


        // Render all
        excelFiles.forEach(f => renderExcel(f));

        // Download All
        document.getElementById('downloadAllBtn').addEventListener('click', () => {
          const wb = XLSX.utils.book_new();
       const fetchPromises = excelFiles.map(f =>
  fetch('high_uploads/excel_files/' + f.name)
    .then(res => res.arrayBuffer())
    .then(ab => {
      const tempWb = XLSX.read(ab, { type: 'array' });
      const ws = tempWb.Sheets[tempWb.SheetNames[0]];
      XLSX.utils.book_append_sheet(wb, ws, f.name.split('.')[0].substring(0, 30));
    })
);

          Promise.all(fetchPromises).then(() => XLSX.writeFile(wb, 'all_excel_combined.xlsx'));
        });
      </script>

    </main>
  </div>
</body>

</html>