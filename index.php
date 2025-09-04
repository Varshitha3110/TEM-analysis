    <?php
    session_start();
    if (!isset($_SESSION['user'])) {
        header("Location: signin_signup.html");
        exit();
    }
    ?>

    <!doctype html>
    <html lang="en">

    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Nanoparticle Analysis — Dashboard</title>

        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

        <link rel="stylesheet" href="style.css" />
        <style>
            .pdf-title {
                font-weight: 600;
                cursor: pointer;
                color: #fff;
                font-size: 14px;
            }

            img {
                max-width: 220px;
                max-height: 220px;
                border: 1px solid #ccc;
                border-radius: 8px;
                display: block;
            }

            .card {
                background: #1f2430;
                border-radius: 12px;
                padding: 20px;
                margin-top: 20px;
                color: #fff;
            }

            .tab-container {
                display: flex;
                border-bottom: 2px solid #2c3240;
                margin-bottom: 15px;
            }

            .tab-btn {
                flex: 1;
                padding: 12px;
                font-size: 14px;
                color: #bbb;
                background: none;
                border: none;
                cursor: pointer;
                transition: 0.3s;
            }

            .tab-btn.active {
                color: #00e0b8;
                border-bottom: 2px solid #ff8800;
                font-weight: 600;
            }

            .tab-btn:hover {
                color: #fff;
            }

            .tab-panel {
                display: none;
            }

            .tab-panel.active {
                display: block;
            }
        </style>
    </head>

    <body>
        <div class="app">
            <?php include 'sidebar.php'; ?>
            <main class="main">
                <?php include 'header.php'; ?>

                <section class="card upload-card">
                    <div class="upload-container">
                        <div class="upload-left">
                            <h3 style="font-weight: 600;"><b>Upload Images</b></h3>
                            <p class="muted">Click "Choose File" to pick image files. (JPEG, PNG)</p>

                            <div class="upload-controls">
                                <label>Scale (nm per pixel):</label>
                                <input type="number" step="0.01" id="scaleInput" value="6.793" /><br>
                            </div>
                            <div class="upload-buttons">
                                <label for="fileInput" class="cta">Choose File</label>
                                <input id="fileInput" type="file" accept="image/*" multiple hidden />
                                <button id="addImageBtn" class="cta" type="button">Add Image</button>
                                <button id="analyzeBtn" class="cta" type="button">Analyze</button>
                            </div>

                            <div id="thresholdContainer"></div>
                        </div>

                        <div id="preview" class="preview-grid"></div>
                    </div>
                </section>

                <section class="card">
                    <h3>Analysis Output</h3>
                    <div id="analysisOutput" style="
        max-height: 200px;
        overflow-y: auto;
        background: #111827;
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 13px;
    ">
                    </div>
                </section>

                <section class="card">
                    <div class="tab-container">
                        <button class="tab-btn active" data-tab="csv">CSV Table</button>
                        <button class="tab-btn" data-tab="pdf">PDF</button>
                    </div>

                    <div id="tab-content">
                        <div class="tab-panel active" id="csv">
                            <div id="excelTableContainer" style="max-height:600px; overflow:auto; border:1px solid #333; padding:10px; border-radius:8px;"></div>
                            <button id="downloadAllBtn" class="cta" style="margin-top:20px;">Download All</button>
                        </div>

                        <div class="tab-panel" id="pdf">
                            <div id="pdfContainer" style="max-height:600px; overflow:auto; border:1px solid #333; padding:10px; border-radius:8px;"></div>
                        </div>

                        <!-- <div class="tab-panel" id="params">
                            <div id="paramsContainer">
                                <p style="color:#ddd;">Parameters data will be shown here...</p>
                            </div> -->
                        </div>
                    </div>
                </section>

                <script>
                    const excelContainer = document.getElementById('excelTableContainer');
                    const pdfTabContainer = document.getElementById('pdfContainer');
                    const paramsContainer = document.getElementById('paramsContainer');
                    const outputContainer = document.getElementById("analysisOutput");

                    let imageFiles = [];
                    let excelFilesList = [];
                    let pdfFilesList = [];
                    let paramsFilesList = [];

                    const phpExcelFilesWithTime = <?php
                    $folder = "uploads/excel_files/";
    $filesWithTime = [];
    $combinedFile = "combined.xlsx";

    date_default_timezone_set('Asia/Kolkata');
    $today = date("Y-m-d");

    if (is_dir($folder)) {
        $allFiles = scandir($folder);
        foreach ($allFiles as $f) {
            if ($f !== "." && $f !== ".." && in_array(pathinfo($f, PATHINFO_EXTENSION), ["xlsx", "xls"]) && $f !== $combinedFile) {
                $fileTime = filemtime($folder . $f);
                if (date("Y-m-d", $fileTime) === $today) {   // ✅ only today
                    $filesWithTime[] = ['name' => $f, 'time' => $fileTime];
                }
            }
        }
        usort($filesWithTime, fn($a, $b) => $b['time'] - $a['time']);
    }
    echo json_encode($filesWithTime);

                    ?>;

                    const phpPdfFilesList = <?php
                    $pdfFolder = "uploads/pdf_files/";
    $pdfFiles = [];
    date_default_timezone_set('Asia/Kolkata');
    $today = date("Y-m-d");

    if (is_dir($pdfFolder)) {
        $allPdfs = scandir($pdfFolder);
        foreach ($allPdfs as $f) {
            if ($f !== "." && $f !== ".." && pathinfo($f, PATHINFO_EXTENSION) === "pdf" && strtolower($f) !== "combined.pdf") {
                $fileTime = filemtime($pdfFolder . $f);
                if (date("Y-m-d", $fileTime) === $today) {  // ✅ only today
                    $pdfFiles[] = $f;
                }
            }
        }
        usort($pdfFiles, fn($a, $b) => filemtime($pdfFolder.$b) - filemtime($pdfFolder.$a));
    }
    echo json_encode(array_values($pdfFiles));

                    ?>;
                    
                    const phpParamsFilesList = <?php
                        $paramsFolder = "uploads/parameters/";
                        $params = [];
                        date_default_timezone_set('Asia/Kolkata');

                        if (is_dir($paramsFolder)) {
                            $allParams = scandir($paramsFolder);
                            foreach ($allParams as $f) {
                                if ($f !== "." && $f !== ".." && in_array(pathinfo($f, PATHINFO_EXTENSION), ["xlsx", "xls"])) {
                                    $params[] = $f;
                                }
                            }
                            usort($params, fn($a, $b) => filemtime($paramsFolder.$b) - filemtime($paramsFolder.$a));
                        }
                        echo json_encode(array_values($params));
                    ?>;

                    function initializeFileLists() {
                        const combinedFile = "combined.xlsx";
                        const combinedFileExists = phpExcelFilesWithTime.some(f => f.name === combinedFile);

                        let sortedFiles = phpExcelFilesWithTime.map(f => f.name);
                        excelFilesList = sortedFiles;

                        if (combinedFileExists) {
                            excelFilesList.push(combinedFile);
                        }
                        
                        pdfFilesList = phpPdfFilesList;
                        paramsFilesList = phpParamsFilesList;
                    }

                    function findPdfByExcelName(excelName) {
                        const baseName = excelName.replace(/\.(xlsx|xls)/, '');
                        const pdfFile = pdfFilesList.find(f => f.replace(/\.pdf/, '') === baseName);
                        return pdfFile ? `uploads/pdf_files/${pdfFile}` : null;
                    }
                    
                    function findExcelByPdfName(pdfName) {
                        const baseName = pdfName.replace(/\.pdf/, '');
                        const excelFile = excelFilesList.find(f => f.replace(/\.(xlsx|xls)/, '') === baseName);
                        return excelFile ? `uploads/excel_files/${excelFile}` : null;
                    }
                    
                    function findParamsByExcelName(excelName) {
                        const baseName = excelName.replace(/\.(xlsx|xls)/, '');
                        const paramsFile = paramsFilesList.find(f => f.replace('_parameters.xlsx', '') === baseName);
                        return paramsFile ? `uploads/parameters/${paramsFile}` : null;
                    }

                    function renderExcel(file) {
                        if (!file) return;
                        
                        const fileWrapper = document.createElement('div');
                        fileWrapper.classList.add('card');
                        fileWrapper.style.marginBottom = '40px';

                        const baseName = file.split(".")[0];
                        const pdfPath = findPdfByExcelName(file);

                        const headerDiv = document.createElement('div');
                        headerDiv.style.display = 'flex';
                        headerDiv.style.alignItems = 'center';
                        headerDiv.style.marginBottom = '10px';

                        const title = document.createElement('span');
                        title.textContent = file;
                        title.classList.add('excel-title');
                        

                        const delBtn = document.createElement('button');
                        delBtn.innerHTML = '<i class="fas fa-trash" style="margin-right:6px;"></i> Delete';
                        delBtn.classList.add('delete-btn');
                        delBtn.onclick = () => {
                            if (confirm(`Delete ${file}?`)) {
                                fetch('delete_to_trash.php?file=' + file)
                                    .then(res => res.json())
                                    .then(resData => {
                                        if (resData.status === 'success') {
                                            fileWrapper.remove();
                                            window.location.reload();
                                        } else {
                                            alert('Failed to delete');
                                        }
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
                            a.href = 'uploads/excel_files/' + file;
                            a.download = file;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        };

                        headerDiv.appendChild(title);
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

                        const origImg = document.createElement('img');
                        origImg.src = "uploads/" + baseName + ".jpg";
                        origImg.style.width = '100%';
                        origImg.style.height = 'auto';
                        origImg.style.marginBottom = '10px';

                        const genPdfIframe = document.createElement('iframe');
                        if (pdfPath) {
                            genPdfIframe.src = pdfPath;
                        }
                        genPdfIframe.style.width = '100%';
                        genPdfIframe.style.height = '400px';
                        genPdfIframe.style.border = '1px solid #ccc';
                        genPdfIframe.style.borderRadius = '8px';
                        genPdfIframe.style.display = 'none';

                        imgAndPdfWrapper.appendChild(origImg);
                        imgAndPdfWrapper.appendChild(genPdfIframe);

                        if (pdfPath) {
                            const openPdfLink = document.createElement('a');
                            openPdfLink.href = pdfPath;
                            openPdfLink.target = '_blank';
                            openPdfLink.textContent = 'Open PDF in new tab';
                            openPdfLink.style.display = "inline-block";
                            openPdfLink.style.color = "#fff";
                            openPdfLink.style.background = "#1e293b";
                            openPdfLink.style.padding = "6px 12px";
                            openPdfLink.style.marginTop = "20px";
                            openPdfLink.style.borderRadius = "6px";
                            openPdfLink.style.marginBottom = "8px";
                            openPdfLink.style.textDecoration = "none";
                            imgAndPdfWrapper.appendChild(openPdfLink);
                        }

                        const optionsDiv = document.createElement('div');
                        optionsDiv.style.marginBottom = '10px';
                        optionsDiv.innerHTML = `
                            <label><input type="checkbox" class="show-image-toggle" checked> Show Image</label><br>
                            <label><input type="checkbox" class="show-pdf-toggle"> Show PDF</label>
                        `;
                        fileWrapper.appendChild(optionsDiv);

                        optionsDiv.querySelector('.show-image-toggle').addEventListener('change', (e) => {
                            origImg.style.display = e.target.checked ? 'block' : 'none';
                        });

                        optionsDiv.querySelector('.show-pdf-toggle').addEventListener('change', (e) => {
                            genPdfIframe.style.display = e.target.checked ? 'block' : 'none';
                        });

                        fetch('uploads/excel_files/' + file)
                            .then(res => res.arrayBuffer())
                            .then(ab => {
                                const workbook = XLSX.read(ab, {
                                    type: 'array'
                                });
                                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                                const data = XLSX.utils.sheet_to_json(sheet);
                                if (data.length === 0) return;

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
                            .catch(error => {
                                tableWrapper.innerHTML = `<p>Error loading Excel file: ${file}</p>`;
                            });

                        contentDiv.appendChild(tableWrapper);
                        contentDiv.appendChild(imgAndPdfWrapper);
                        fileWrapper.appendChild(contentDiv);

                        excelContainer.appendChild(fileWrapper);
                    }

                    function renderAllExcelFiles() {
                        excelContainer.innerHTML = '';
                        for (const file of excelFilesList) {
                            renderExcel(file);
                        }
                    }
                    
                    function renderPdf(file) {
                        if (!file) return;

                        const fileWrapper = document.createElement('div');
                        fileWrapper.classList.add('card');
                        fileWrapper.style.marginBottom = '20px';

                        const baseName = file.replace(".pdf", "");
                        const excelPath = findExcelByPdfName(file);

                        const headerDiv = document.createElement('div');
                        headerDiv.style.display = 'flex';
                        headerDiv.style.alignItems = 'center';
                        headerDiv.style.marginBottom = '10px';

                        const title = document.createElement('span');
                        title.textContent = file;
                        title.classList.add('pdf-title');
                        
                        if (excelPath) {
                            title.addEventListener('dblclick', () => {
                                window.open(excelPath, '_blank');
                            });
                        }

                        const delBtn = document.createElement('button');
                        delBtn.innerHTML = '<i class="fas fa-trash" style="margin-right:6px;"></i> Delete';
                        delBtn.classList.add('delete-btn');
                        delBtn.onclick = () => {
                            if (confirm(`Delete ${file}?`)) {
                                fetch(`delete_to_trash.php?file=${encodeURIComponent(file)}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (res.status === 'success') {
                                            fileWrapper.remove();
                                            window.location.reload();
                                        } else {
                                            alert('Failed to delete');
                                        }
                                    });
                            }
                        };
                        headerDiv.appendChild(title);
                        headerDiv.appendChild(delBtn);

                        const downloadBtn = document.createElement('button');
                        downloadBtn.innerHTML = '<i class="fas fa-download" style="margin-right:6px;"></i> Download';
                        downloadBtn.classList.add('delete-btn');
                        downloadBtn.style.background = "#198754";
                        downloadBtn.style.marginLeft = "8px";
                        downloadBtn.onclick = () => {
                            window.open('uploads/pdf_files/' + file, '_blank');
                        };
                        headerDiv.appendChild(downloadBtn);

                        fileWrapper.appendChild(headerDiv);

                        const pdfIframe = document.createElement('iframe');
                        pdfIframe.src = 'uploads/pdf_files/' + file;
                        pdfIframe.style.width = '100%';
                        pdfIframe.style.height = '400px';
                        pdfIframe.style.border = '1px solid #ccc';
                        pdfIframe.style.borderRadius = '8px';
                        pdfIframe.style.marginTop = '10px';
                        fileWrapper.appendChild(pdfIframe);

                        pdfTabContainer.appendChild(fileWrapper);
                    }
                    
                    function renderAllPdfFiles() {
                        pdfTabContainer.innerHTML = '';
                        for (const file of pdfFilesList) {
                            renderPdf(file);
                        }
                    }
                    
                    function addFiles(files) {
                        for (const f of files) imageFiles.push(f);
                        renderImagesAndThresholds();
                    }

                    function renderImagesAndThresholds() {
                        const preview = document.getElementById("preview");
                        const container = document.getElementById("thresholdContainer");
                        preview.innerHTML = '';
                        container.innerHTML = '';

                        imageFiles.forEach((file, idx) => {
                            const div = document.createElement("div");
                            div.style.display = "flex";
                            div.style.flexDirection = "column";
                            div.style.alignItems = "center";
                            div.style.marginBottom = "10px";
                            div.style.borderRadius = "5px";
                            div.style.padding = "10px";
                            div.style.background = "#2c3240";
                            div.style.flex = "0 0 48%";
                            div.style.boxSizing = "border-box";

                            const img = document.createElement("img");
                            img.src = URL.createObjectURL(file);
                            img.style.width = "100%";
                            img.style.height = "auto";
                            img.style.marginBottom = "5px";
                            img.style.borderRadius = "5px";
                            div.appendChild(img);

                            const deleteBtn = document.createElement("button");
                            deleteBtn.textContent = "Remove";
                            deleteBtn.classList.add('cta', 'delete-btn');
                            deleteBtn.style.marginTop = '5px';
                            deleteBtn.onclick = () => {
                                imageFiles.splice(idx, 1);
                                renderImagesAndThresholds();
                            };
                            div.appendChild(deleteBtn);

                            preview.appendChild(div);

                            const rowDiv = document.createElement("div");
                            rowDiv.style.display = "flex";
                            rowDiv.style.alignItems = "center";
                            rowDiv.style.marginBottom = "10px";
                            rowDiv.style.gap = "8px";

                            const label = document.createElement("label");
                            label.innerText = `Threshold for ${file.name}: `;

                            const input = document.createElement("input");
                            input.type = "number";
                            input.min = 0;
                            input.max = 255;
                            input.value = 123;
                            input.dataset.index = idx;
                            input.style.width = "60px";
                            input.style.borderRadius = "5px";
                            input.style.border = "none";
                            input.style.color = "#090a0aff";
                            input.style.padding = "4px";

                            rowDiv.appendChild(label);
                            rowDiv.appendChild(input);
                            container.appendChild(rowDiv);
                        });

                        preview.style.display = "flex";
                        preview.style.flexWrap = "wrap";
                        preview.style.gap = "12px";
                    }

                    document.getElementById("fileInput").addEventListener("change", e => {
                        addFiles(e.target.files);
                        e.target.value = '';
                    });

                    document.getElementById("addImageBtn").addEventListener("click", e => {
                        e.preventDefault();
                        document.getElementById("fileInput").click();
                    });

                    document.getElementById("analyzeBtn").addEventListener("click", e => {
                        e.preventDefault();
                        if (imageFiles.length === 0) {
                            alert("Please select images!");
                            return;
                        }

                        outputContainer.innerText = "Executing analysis...\n";

                        const thresholds = Array.from(document.querySelectorAll("#thresholdContainer input")).map(i => parseInt(i.value));
                        const formData = new FormData();
                        imageFiles.forEach(file => formData.append("images[]", file));
                        formData.append("thresholds", JSON.stringify(thresholds));
                        formData.append("scale", parseFloat(document.getElementById("scaleInput").value));

                        fetch("upload.php", {
                                method: "POST",
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                outputContainer.innerText += data.output.join("\n");
                                outputContainer.scrollTop = outputContainer.scrollHeight;

                                if (data.status === "success") {
                                    alert("Analysis complete!");
                                    
                                    // Store the uploaded filenames for later display
                                    localStorage.setItem('lastUploadedImages', JSON.stringify(data.uploaded_files));
                                    localStorage.setItem('lastAnalysisOutput', 'Analysis completed successfully.');
                                    
                                    window.location.reload();
                                } else {
                                    alert("Error executing Python script");
                                }
                            })
                            .catch(err => {
                                outputContainer.innerText += "\n⚠ Fetch error: " + err;
                            });
                    });

                    document.getElementById('downloadAllBtn').addEventListener('click', () => {
                        const wb = XLSX.utils.book_new();
                        const fetchPromises = excelFilesList.map(f =>
                            fetch('uploads/excel_files/' + f).then(r => r.arrayBuffer()).then(ab => {
                                const tempWb = XLSX.read(ab, {
                                    type: 'array'
                                });
                                const ws = tempWb.Sheets[tempWb.SheetNames[0]];
                                XLSX.utils.book_append_sheet(wb, ws, f.split('.')[0].substring(0, 30));
                            })
                        );
                        Promise.all(fetchPromises).then(() => XLSX.writeFile(wb, 'all_excel_combined.xlsx'));
                    });

                    const tabButtons = document.querySelectorAll(".tab-btn");
                    const tabPanels = document.querySelectorAll(".tab-panel");

                    tabButtons.forEach(btn => {
                        btn.addEventListener("click", () => {
                            tabButtons.forEach(b => b.classList.remove("active"));
                            btn.classList.add("active");
                            tabPanels.forEach(p => p.classList.remove("active"));
                            const target = btn.dataset.tab;
                            document.getElementById(target).classList.add("active");
                        });
                    });
                    
                    document.addEventListener('DOMContentLoaded', () => {
                        initializeFileLists();
                        renderAllExcelFiles();
                        renderAllPdfFiles();

                        const lastImages = JSON.parse(localStorage.getItem('lastUploadedImages') || '[]');
                        const lastOutput = localStorage.getItem('lastAnalysisOutput') || '';

                        if (lastImages.length > 0) {
                            const html = `
                                <span style="font-size:16px; font-weight:600; color:white;">
                                    Previously uploaded images:<br>
                                    <ul style="margin:0; padding-left:18px;">
                                        ${lastImages.map(f => `<li>${f}</li>`).join("")}
                                    </ul>
                                </span>
                                <br>${lastOutput}
                            `;
                            outputContainer.innerHTML = html;
                            outputContainer.scrollTop = outputContainer.scrollHeight;

                            localStorage.removeItem('lastUploadedImages');
                            localStorage.removeItem('lastAnalysisOutput');
                        }
                    });
                </script>
            </main>
        </div>
    </body>

    </html>