<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Export CSV — Nanoparticle Dashboard</title>
  
  <link href="style.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

  <!-- XLSX -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <style>
    body { font-family: 'Poppins', sans-serif; }
    .excel-title { cursor: pointer; display: inline-block; padding: 8px 14px; background: #f8fafc; border-radius: 8px; color: #0d6efd; font-weight: 600; margin-right: 12px; transition: background 0.25s, transform 0.25s; text-decoration: none; }
    .excel-title:hover { background: #e9f2ff; transform: translateY(-2px); }
    .delete-btn { cursor: pointer; color: #fff; background: #dc3545; border: none; padding: 4px 8px; border-radius: 6px; margin-left: 12px; font-size: 13px; transition: background 0.2s; }
    .delete-btn:hover { background: #b02a37; }
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 6px 8px; border-bottom: 1px solid #ccc; }
    th { background: #f4f2f2; color: #020202; }
    .card { padding: 15px; margin-bottom: 30px; border-radius: 10px; border: 1px solid #ccc; }
    .flex-row { display: flex; gap: 20px; align-items: flex-start; }
    .flex-col { flex: 1; }
  </style>
</head>

<body>
  <div class="app">
    <?php include 'sidebar.php'; ?>
    <main class="main">
      <?php include 'header.php'; ?>

      <section class="card">
        <h3>Exported CSV Tables</h3>
        <div id="excelTableContainer" style="max-height:600px; overflow:auto;"></div>
        <button id="downloadAllBtn" class="cta" style="margin-top:20px;">Download All</button>
      </section>

      <script>
        const excelContainer = document.getElementById('excelTableContainer');

        // PHP outputs Excel files as JSON
        const excelFiles = <?php
          $folder = "uploads/excel_files/";
          $filesWithTime = [];
          if (is_dir($folder)) {
            foreach (scandir($folder) as $f) {
              if ($f !== "." && $f !== ".." && in_array(pathinfo($f, PATHINFO_EXTENSION), ["xlsx","xls"]) && stripos($f, 'combined')===false) {
                $filesWithTime[] = ['name'=>$f, 'time'=>filemtime($folder.$f)];
              }
            }
            usort($filesWithTime, fn($a,$b)=>$b['time']-$a['time']);
            echo json_encode($filesWithTime);
          }
        ?>;

        const pdfFiles = <?php
          $pdfs = array_filter(scandir("uploads/pdf_files/"), fn($f)=>!in_array($f, [".",".."]));
          echo json_encode(array_values($pdfs));
        ?>;

        async function findPdfByExcelName(name) {
          for (const f of pdfFiles) {
            if (f.replace(".pdf","")===name) return 'uploads/pdf_files/' + f;
          }
          return null;
        }

        let configData = {};
        fetch('uploads/config.json').then(res=>res.json()).then(data=>{
          configData = data;
          excelFiles.forEach(f=>renderExcel(f));
        });

        async function renderExcel(fileObj) {
          const file = fileObj.name;
          const baseName = file.split(".")[0];
          const formattedTime = new Date(fileObj.time*1000).toLocaleString();

          const card = document.createElement('div'); card.classList.add('card');

          // Header + buttons
          const headerDiv = document.createElement('div'); headerDiv.style.display='flex'; headerDiv.style.alignItems='center'; headerDiv.style.marginBottom='10px';
          const title = document.createElement('span'); title.textContent=file; title.classList.add('excel-title');
const delBtn = document.createElement('button');
delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
delBtn.classList.add('delete-btn');
delBtn.onclick = () => {
  if (confirm(`Delete ${file}?`)) {
    // Call delete_to_trash.php to move file to trash
    fetch('delete_to_trash.php?file=' + encodeURIComponent(file))
      .then(res => res.json())
      .then(resData => {
        if (resData.status === 'success') {
          card.remove(); // Remove from UI
        } else {
          alert('Failed to delete');
        }
      })
      .catch(() => alert('Error calling delete_to_trash.php'));
  }
};
          delBtn.onclick = ()=>{ if(confirm(`Delete ${file}?`)) fetch('delete_to_trash.php?file='+file).then(r=>r.json()).then(res=>{ if(res.status==='success') card.remove(); else alert('Failed'); }) };
          const downloadBtn = document.createElement('button'); downloadBtn.innerHTML='<i class="fas fa-download"></i> Download'; downloadBtn.classList.add('delete-btn'); downloadBtn.style.background="#198754"; downloadBtn.style.marginLeft="8px";
          downloadBtn.onclick = ()=>{ const a=document.createElement('a'); a.href='uploads/excel_files/'+file; a.download=file; document.body.appendChild(a); a.click(); document.body.removeChild(a); };
          headerDiv.append(title, delBtn, downloadBtn); card.appendChild(headerDiv);

          // Config + date
          const configP = document.createElement('p'); configP.style.fontWeight='500';
          const scale = configData.scale ?? '', thresholds = configData.thresholds ? configData.thresholds.join(', ') : '';
          configP.textContent=`Scale: ${scale} | Thresholds: ${thresholds} | Date: ${formattedTime}`;
          card.appendChild(configP);

          // Content row
          const contentDiv = document.createElement('div'); contentDiv.classList.add('flex-row');

          const tableWrapper = document.createElement('div'); tableWrapper.classList.add('flex-col');

          const imgWrapper = document.createElement('div'); imgWrapper.style.flex='0 0 400px'; imgWrapper.style.display='flex'; imgWrapper.style.flexDirection='column'; imgWrapper.style.alignItems='center';
          const img = document.createElement('img'); img.src='uploads/'+baseName+'.jpg'; img.style.width='100%'; img.style.marginBottom='10px';
          img.addEventListener('dblclick', ()=>window.open(img.src,'_blank'));
          imgWrapper.appendChild(img);

          const pdfIframe = document.createElement('iframe'); pdfIframe.style.width='100%'; pdfIframe.style.height='400px'; pdfIframe.style.border='1px solid #ccc'; pdfIframe.style.borderRadius='8px'; pdfIframe.style.display='none';
          const pdfLink = document.createElement('a'); pdfLink.target='_blank'; pdfLink.textContent='Open PDF in new tab'; pdfLink.style.display='none'; pdfLink.style.marginTop='10px'; pdfLink.style.padding='6px 12px'; pdfLink.style.borderRadius='6px'; pdfLink.style.color='#fff'; pdfLink.style.background='#0d0d0d'; pdfLink.style.textDecoration='none';
          imgWrapper.append(pdfIframe,pdfLink);

          const pdfPath = await findPdfByExcelName(baseName);
          if(pdfPath){ pdfIframe.src=pdfPath; pdfLink.href=pdfPath; pdfLink.style.display='inline-block'; }

          // Checkbox toggles
          const toggleDiv = document.createElement('div'); toggleDiv.style.marginBottom='10px';
          toggleDiv.innerHTML=`<label><input type="checkbox" checked class="show-img"> Show Image</label><br>
                               <label><input type="checkbox" class="show-pdf"> Show PDF</label>`;
          toggleDiv.querySelector('.show-img').addEventListener('change', e=>{ img.style.display=e.target.checked?'block':'none'; });
          toggleDiv.querySelector('.show-pdf').addEventListener('change', e=>{ pdfIframe.style.display=e.target.checked?'block':'none'; });
          card.appendChild(toggleDiv);

          contentDiv.append(tableWrapper,imgWrapper); card.appendChild(contentDiv);
          excelContainer.appendChild(card);

          // Load Excel table
          fetch('uploads/excel_files/'+file).then(r=>r.arrayBuffer()).then(ab=>{
            const wb=XLSX.read(ab,{type:'array'});
            const sheet=wb.Sheets[wb.SheetNames[0]];
            const data=XLSX.utils.sheet_to_json(sheet);
            if(!data.length) return;
            const table=document.createElement('table');
            const thead=document.createElement('thead'); const trh=document.createElement('tr');
            Object.keys(data[0]).forEach(k=>{ const th=document.createElement('th'); th.textContent=k; trh.appendChild(th); });
            thead.appendChild(trh); table.appendChild(thead);
            const tbody=document.createElement('tbody');
            data.forEach(row=>{ const tr=document.createElement('tr'); Object.values(row).forEach(v=>{ const td=document.createElement('td'); td.textContent=v; tr.appendChild(td); }); tbody.appendChild(tr); });
            table.appendChild(tbody); tableWrapper.appendChild(table);
          }).catch(()=>{ tableWrapper.innerHTML=`<p>Error loading Excel: ${file}</p>`; });
        }

        // Download all
        document.getElementById('downloadAllBtn').addEventListener('click', ()=>{
          const wb=XLSX.utils.book_new();
          const fetches=excelFiles.map(f=>fetch('uploads/excel_files/'+f.name).then(r=>r.arrayBuffer()).then(ab=>{ const temp=XLSX.read(ab,{type:'array'}); XLSX.utils.book_append_sheet(wb,temp.Sheets[temp.SheetNames[0]],f.name.split('.')[0].substring(0,30)); }));
          Promise.all(fetches).then(()=>XLSX.writeFile(wb,'all_excel_combined.xlsx'));
        });
      </script>
    </main>
  </div>
</body>
</html>
