<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Nanoparticle Analysis — Dashboard</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="style.css" />

  <style>
    .csv-table-wrapper.show-images .table-col {
  flex: 0 0 50%;
}

.csv-table-wrapper.show-images .img-col {
  flex: 0 0 50%;
  display: flex;
}

.csv-table-wrapper .img-col {
  display: none;
}

.csv-table-wrapper {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-bottom: 20px;
}

.csv-table-wrapper .table-col {
  flex: 1;
  max-width: 50%;
}

.csv-table-wrapper .img-col {
  flex: 1;
  max-width: 50%;
  display: flex;
  flex-direction: column;
  gap: 8px;
}


   
   /* Style the file input wrapper */
.upload-container input[type="file"] {
  display: inline-block;
  border-radius: 8px;
  border: 1px solid #444;
  background-color: #1f2430;
  color: #fff;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
  flex: 1;
  max-width: 105px;
  padding-top: -120px;
}

/* Hover effect */
.upload-container input[type="file"]:hover {
  background-color: #252a36;
  transform: scale(1.02);
}

/* Optional: Remove default file input button for modern look */
.upload-container input[type="file"]::-webkit-file-upload-button {
  visibility: hidden;
}

/* Add custom pseudo-button */
.upload-container input[type="file"]::before {
  content: "Choose File";
  display: inline-block;
  background: linear-gradient(90deg, var(--accent-teal, #00e0b8), #20d6b7);
  color: #042624;
  padding: 10px 12px;
  border-radius: 8px;
  margin-right: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
  margin-top: 10px;
}

/* Hover effect for pseudo-button */
.upload-container input[type="file"]::before:hover {
  background: linear-gradient(90deg, #00c8a0, #20bfa7);
  transform: scale(1.05);
}

   
   .upload-card {
  display: flex;
  flex-direction: row;      /* ← put items side by side */
  align-items: flex-start;  /* align to top */
  justify-content: space-between;
  gap: 20px;                /* spacing between form and preview */
  padding: 25px;
}

/* Left side (form, title, paragraph) */
.upload-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Keep your form inline (input + button side by side) */
.upload-container form {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

/* Right side preview gallery */
/* Container for each preview image */
#preview div {
  position: relative;
  display: inline-block;
}

/* Preview images */
#preview img {
  max-width: 120px;
  max-height: 120px;
  border-radius: 8px;
  border: 1px solid #555;
  display: block;
}

/* Delete button (×) */
#preview button.delete-btn {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 22px;
  height: 22px;
  background: rgba(255, 0, 0, 0.85);
  color: #fff;
  border: none;
  border-radius: 50%;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  transition: transform 0.2s, background 0.2s;
}

/* Hover effect */
#preview button.delete-btn:hover {
  background: rgba(255, 0, 0, 1);
  transform: scale(1.1);
}



    .file-title {
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

    .file-title button {
      padding: 4px 8px;
      font-size: 12px;
      background: #00e0b8;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      color: #111;
    }

    .file-title button:hover {
      background: #e9f2ff;
    }




    body {
  background: linear-gradient(180deg,#0c1c29 100%, #050b10 100%);
      color: #fff;
      font-family: "Poppins", sans-serif;
    }

    img {
      max-width: 220px;
      max-height: 220px;
      border: 1px solid #444;
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

    #preview {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      gap: 10px;
      margin-top: 15px;
    }

    .cta {
      padding: 10px 15px;
      background: #00e0b8;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      color: #111;
      font-weight: 600;
      margin-top: 10px;
    }

    .cta:hover {
      background: #00c8a0;
    }

    pre {
      color: #fff;
      background: #222;
      padding: 10px;
      border-radius: 6px;
      overflow-x: auto;
    }
  </style>
</head>

<body>
  <div class="app">
    <?php include 'sidebar_high.php'; ?>
    <main class="main">
      <?php include 'header_high.php'; ?>

      <!-- Upload Section -->
 <section class="card upload-card"> 
  <div class="upload-container">
    <h3><b>Upload Images</b></h3>
    <p>Click "Choose File" to pick image files. (JPEG, PNG)</p>

    <form id="uploadForm" enctype="multipart/form-data" method="POST">
      <input id="fileInput" type="file" name="images[]" accept="image/*" multiple>
      <button type="submit" class="cta">Upload & Analyze</button>
    </form>
  </div>

  <!-- Preview goes to the right side -->
  <div id="preview"></div>
</section>


      <!-- Analysis Output -->
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
        ">Waiting for analysis...</div>
      </section>

      <!-- Tabs Section -->
      <section class="card">
        <div class="tab-container">
          <button class="tab-btn active" data-tab="csv">CSV Table</button>
          <button class="tab-btn" data-tab="pdf">PDF</button>
          <button class="tab-btn" data-tab="params">Parameters</button>
        </div>

        <div id="tab-content">
    <div class="tab-panel active" id="csv">

<div style="margin:10px 0;">
  <label style="margin-right:20px;">
    <input type="checkbox" id="showUploaded"> Show Uploaded Images
  </label> <br>
  <label>
    <input type="checkbox" id="showGenerated"> Show Generated Images
  </label>
</div>
<div id="excelTableContainer" style="max-height:600px; overflow:auto; border:1px solid #333; padding:10px; border-radius:8px;"></div>


  <button id="downloadAllBtn" class="cta">Download All</button>
</div>

          <div class="tab-panel" id="pdf">
            <div id="pdfContainer" style="max-height:600px; overflow:auto; border:1px solid #333; padding:10px; border-radius:8px;"></div>
          </div>

          <div class="tab-panel" id="params">
            <div id="paramsContainer">
              <p style="color:#ddd;">Parameters data will be shown here...</p>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>

  <script>
// document.getElementById("fileInput").addEventListener("change", function() {
//   const preview = document.getElementById("preview");
//   preview.innerHTML = ""; // clear previous previews
//   Array.from(this.files).forEach(file => {
//     const reader = new FileReader();
//     reader.onload = function(e) {
//       const img = document.createElement("img");
//       img.src = e.target.result;
//       preview.appendChild(img);
//     };
//     reader.readAsDataURL(file);
//   });
// });



    // === Upload & Fetch Analysis ===
    // === Upload & Fetch Analysis ===
    document.getElementById("uploadForm").addEventListener("submit", async function(e) {
      e.preventDefault();
      let formData = new FormData(this);
      const outputDiv = document.getElementById("analysisOutput");
      const csvContainer = document.getElementById("excelTableContainer");
      const pdfContainer = document.getElementById("pdfContainer");
      const paramsContainer = document.getElementById("paramsContainer");

      outputDiv.innerText = "Processing...";
      csvContainer.innerHTML = "";
      pdfContainer.innerHTML = "";
      paramsContainer.innerHTML = "";

      try {
        let res = await fetch("high_upload.php", {
          method: "POST",
          body: formData
        });
        let text = await res.text();
        console.log("Raw Response:", text);

        let data = JSON.parse(text);

        if (data.status !== "success") {
          outputDiv.innerText = "Error: " + data.message;
          return;
        }

        outputDiv.innerText = data.python_output || "Processing complete.";

        // --- CSV Tab ---
// --- CSV Tab ---
// --- CSV Tab ---





































// --- CSV Tables ---
if (data.csv_files && data.csv_files.length > 0) {
  const uploadedCheckbox = document.getElementById("showUploaded");
  const generatedCheckbox = document.getElementById("showGenerated");

  data.csv_files.forEach((csvFile, idx) => {
    fetch(csvFile)
      .then(r => r.text())
      .then(csvText => {
        const rows = csvText.trim().split("\n").map(r => r.split(","));

        // Table
        const table = document.createElement("table");
        table.style.borderCollapse = "collapse";
        table.style.width = "100%";
        rows.forEach(row => {
          const tr = document.createElement("tr");
          row.forEach(cell => {
            const td = document.createElement("td");
            td.textContent = cell;
            td.style.border = "1px solid #555";
            td.style.padding = "4px 8px";
            tr.appendChild(td);
          });
          table.appendChild(tr);
        });

        // Wrapper: Table + Image Column
        const tableWrapper = document.createElement("div");
        tableWrapper.className = "csv-table-wrapper"; // <-- important
        tableWrapper.dataset.basename = csvFile.split("/").pop().replace(/\.[^/.]+$/, "");
        tableWrapper.style.display = "flex";
        tableWrapper.style.alignItems = "flex-start";
        tableWrapper.style.marginBottom = "20px";
        tableWrapper.style.gap = "15px";

        // Table Column
        const tableCol = document.createElement("div");
        tableCol.className = "table-col";
        tableCol.style.flex = "1";

        // Title + Buttons
        const titleDiv = document.createElement("div");
        titleDiv.className = "file-title";
        titleDiv.style.display = "flex";
        titleDiv.style.justifyContent = "space-between";
        titleDiv.style.alignItems = "center";
        titleDiv.innerHTML = `<span>${csvFile.split("/").pop()}</span>`;

        const btnContainer = document.createElement("div");
        btnContainer.style.display = "flex";
        btnContainer.style.gap = "5px";

        const dlBtn = document.createElement("button");
        dlBtn.innerHTML = `<i class="fas fa-download"></i>`;
        dlBtn.onclick = () => {
          const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
          XLSX.writeFile(wb, csvFile.split("/").pop());
        };

        const delBtn = document.createElement("button");
        delBtn.innerHTML = `<i class="fas fa-trash-alt"></i>`;
        delBtn.onclick = () => tableWrapper.remove();

        btnContainer.appendChild(dlBtn);
        btnContainer.appendChild(delBtn);
        titleDiv.appendChild(btnContainer);

        tableCol.appendChild(titleDiv);
        tableCol.appendChild(table);

        // Image Column
        const imgCol = document.createElement("div");
        imgCol.className = "img-col";
        imgCol.style.flex = "1";
        imgCol.style.display = "flex";
        imgCol.style.flexDirection = "column";
        imgCol.style.gap = "8px";

        tableWrapper.appendChild(tableCol);
        tableWrapper.appendChild(imgCol);
        csvContainer.appendChild(tableWrapper);

        // Function to render images for this table
        const renderImagesForTable = (tableCol, imgCol, baseName) => {
          imgCol.innerHTML = "";
          const getFileName = (path) => path.split(/[/\\]/).pop().replace(/\.[^/.]+$/, "");

const getFileBaseName = (path) => path.split('/').pop().replace(/\.[^/.]+$/, '').trim();

if (uploadedCheckbox.checked && data.uploaded_images) {
  data.uploaded_images
    .filter(f => getFileBaseName(f) === baseName)
    .forEach(imgPath => {
      const fileName = imgPath.split("/").pop();
      const img = document.createElement("img");
      img.src = "high_uploads/" + encodeURIComponent(fileName);
      img.style.maxWidth = "100%";
      img.style.border = "1px solid #555";
      img.style.borderRadius = "6px";
      imgCol.appendChild(img);
    });
}




          if (generatedCheckbox.checked && data.generated_images) {
         data.generated_images
  .filter(f => f.includes(baseName))
  .forEach(imgPath => {
    const fileName = imgPath.split(/[/\\]/).pop();
    const encodedFileName = encodeURIComponent(fileName);
    const img = document.createElement("img");
    img.src = "high_uploads/jpg_files/" + encodedFileName;
    img.style.maxWidth = "100%";
    img.style.border = "1px solid #555";
    img.style.borderRadius = "6px";
    imgCol.appendChild(img);
  });

          }

          // Show or hide column
tableWrapper.classList.toggle("show-images", uploadedCheckbox.checked || generatedCheckbox.checked);
        };

        // Attach checkbox change events (only once globally)
     uploadedCheckbox.addEventListener("change", () => {
  document.querySelectorAll(".csv-table-wrapper").forEach(wrapper => {
    const tableCol = wrapper.querySelector(".table-col");
    const imgCol = wrapper.querySelector(".img-col");
    const baseName = wrapper.dataset.basename;
    renderImagesForTable(tableCol, imgCol, baseName);
  });
});


        generatedCheckbox.addEventListener("change", uploadedCheckbox.onchange);

        // Optional: render initially if checkboxes are already checked
        if (uploadedCheckbox.checked || generatedCheckbox.checked) {
          renderImagesForTable(tableCol, imgCol, tableWrapper.dataset.basename);
        }
      });
  });
}






















// --- PDF Tab ---
if (data.images && data.images.length > 0) {
  // Wrapper grid (2 per row)
  let grid = document.createElement("div");
  grid.style.display = "grid";
  grid.style.gridTemplateColumns = "repeat(2, 1fr)";
  grid.style.gap = "15px";

  data.images.forEach(imgPath => {
    let container = document.createElement("div");
    container.style.border = "1px solid #ccc";
    container.style.padding = "10px";
    container.style.borderRadius = "8px";
    container.style.textAlign = "center";

    // Title with delete button
    let titleDiv = document.createElement("div");
    titleDiv.className = "file-title";
    titleDiv.style.display = "flex";
    titleDiv.style.marginTop="15px";
        titleDiv.style.marginBotton="15px";

    titleDiv.style.justifyContent = "space-between";
    titleDiv.style.alignItems = "center";

    let fileName = imgPath.split("/").pop();
    let nameSpan = document.createElement("span");
    nameSpan.innerText = fileName;

    let delBtn = document.createElement("button");
    delBtn.innerText = "Delete";
    delBtn.style.background = "#e74c3c";
    delBtn.style.color = "white";
    delBtn.style.border = "none";
    delBtn.style.padding = "5px 10px";
    delBtn.style.borderRadius = "5px";
    delBtn.onclick = () => {
      container.remove();
      // optional: also request server to delete
      // fetch(`/delete_file.php?file=${encodeURIComponent(imgPath)}`);
    };

    titleDiv.appendChild(nameSpan);
    titleDiv.appendChild(delBtn);

    // Image
    let img = document.createElement("img");
    img.src = imgPath;
    img.style.maxWidth = "100%";
    img.style.marginTop = "5px";
    img.style.borderRadius = "5px";

    // ✅ Double-click to open in new tab
    img.ondblclick = () => {
      window.open(imgPath, "_blank");
    };

    container.appendChild(titleDiv);
    container.appendChild(img);
    grid.appendChild(container);
  });

  pdfContainer.appendChild(grid);

  // === Download All button ===
let downloadAllBtn = document.createElement("button");
downloadAllBtn.innerText = "Download All";

// ✅ New style applied
downloadAllBtn.style.display = "inline-block";
downloadAllBtn.style.textAlign = "center";
downloadAllBtn.style.background = "linear-gradient(90deg, var(--accent-teal), #20d6b7)";
downloadAllBtn.style.color = "#042624";
downloadAllBtn.style.border = "0";
downloadAllBtn.style.padding = "10px 16px";
downloadAllBtn.style.borderRadius = "12px";
downloadAllBtn.style.fontWeight = "600";
downloadAllBtn.style.cursor = "pointer";
downloadAllBtn.style.boxShadow = "0 6px 20px rgba(7, 183, 156, 0.18)";
downloadAllBtn.style.transition = "all 0.2s ease";
downloadAllBtn.style.marginRight = "10px";
downloadAllBtn.style.marginBottom="10px";
downloadAllBtn.style.fontSize = "14px";
downloadAllBtn.style.userSelect = "none"; // prevents text selection
downloadAllBtn.style.marginTop = "13px";

// ✅ Optional hover effect
downloadAllBtn.onmouseover = () => {
  downloadAllBtn.style.transform = "scale(1.05)";
};
downloadAllBtn.onmouseout = () => {
  downloadAllBtn.style.transform = "scale(1)";
};

  downloadAllBtn.onclick = () => {
    data.images.forEach(imgPath => {
      const link = document.createElement("a");
      link.href = imgPath;
      link.download = imgPath.split("/").pop();
      link.click();
    });
  };

  pdfContainer.appendChild(downloadAllBtn);
}


  // display: inline-block; /* makes label behave like a button */
  // text-align: center;
  // background: linear-gradient(90deg, var(--accent-teal), #20d6b7);
  // color: #042624;
  // border: 0;
  // padding: 10px 16px;
  // border-radius: 12px;
  // font-weight: 600;
  // cursor: pointer;
  // box-shadow: 0 6px 20px rgba(7, 183, 156, 0.18);
  // transition: all 0.2s ease;
  // margin-right: 10px;
  // font-size: 14px;
  // user-select: none; /* prevents text selection */
  // margin-top: 13px;





























        // --- Parameters Tab ---
if (data.params) {
  paramsContainer.innerHTML = ""; // clear
  console.log("Params object:", data.params);

for (const file of Object.keys(data.params)) {
  if (!file || !file.toLowerCase().endsWith(".xlsx")) {
    console.log("Skipping non-Excel file:", file);
    continue;
  }

console.log("Processing Excel file:", file);

const filePath = 'http://localhost/application/high_uploads/parameters_files/' + encodeURIComponent(file);

  try {
    const response = await fetch(filePath);
    if (!response.ok) throw new Error(`Failed to fetch ${filePath}: ${response.statusText}`);
    const arrayBuffer = await response.arrayBuffer();
    const workbook = XLSX.read(arrayBuffer, { type: "array" });
    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
    const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

    // === build table as before ===
    const table = document.createElement("table");
    table.style.borderCollapse = "collapse";
    table.style.marginBottom = "15px";

    jsonData.forEach(row => {
      const tr = document.createElement("tr");
      row.forEach(cell => {
        const td = document.createElement("td");
        td.innerText = cell !== undefined ? cell : "";
        td.style.border = "1px solid #555";
        td.style.padding = "4px 8px";
        tr.appendChild(td);
      });
      table.appendChild(tr);
    });

    // Title + download
    const titleDiv = document.createElement("div");
    titleDiv.className = "file-title";
    titleDiv.innerHTML = `<span>${file}</span>`;
        
    const dlBtn = document.createElement("button");
    dlBtn.innerText = "Download";
    dlBtn.onclick = () => {
      const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
      XLSX.writeFile(wb, file);
    };
    titleDiv.appendChild(dlBtn);

    paramsContainer.appendChild(titleDiv);
    paramsContainer.appendChild(table);

  } catch (err) {
    const errMsg = document.createElement("p");
    errMsg.innerText = `Failed to load ${file}: ${err.message}`;
    errMsg.style.color = "red";
    titleDiv.style.marginTop="15px";
        titleDiv.style.marginBotton="15px";
    paramsContainer.appendChild(errMsg);
  }

}
}




















        // --- Download All Buttons ---
        document.getElementById("downloadAllBtn").onclick = () => {
          const activeTab = document.querySelector(".tab-panel.active");
          const tables = activeTab.querySelectorAll("table");
          tables.forEach((table, idx) => {
            const wb = XLSX.utils.table_to_book(table, {
              sheet: "Sheet1"
            });
            const prefix = activeTab.id === "csv" ? "CSV" : activeTab.id === "params" ? "PARAMS" : "PDF";
            XLSX.writeFile(wb, `${prefix}_Table_${idx + 1}.xlsx`);
          });
        };


      } catch (err) {
        outputDiv.innerText = "Fetch error: " + err.message;
      }
    });


    // === Tabs Switching ===
    document.querySelectorAll(".tab-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
        document.querySelectorAll(".tab-panel").forEach(p => p.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).classList.add("active");
      });
    });

    // === Download All CSVs ===
    document.getElementById("downloadAllBtn").addEventListener("click", () => {
      let tables = document.querySelectorAll("#excelTableContainer table");
      tables.forEach((table, idx) => {
        let wb = XLSX.utils.table_to_book(table, {
          sheet: "Sheet1"
        });
        XLSX.writeFile(wb, `Analysis_Table_${idx + 1}.xlsx`);
      });
    });
  </script>
  <script>
document.addEventListener("DOMContentLoaded", function() {

  // === Preview Selected Images ===
const fileInput = document.getElementById("fileInput");
if(fileInput){
const fileInput = document.getElementById("fileInput");
const preview = document.getElementById("preview");

// store selected files
let selectedFiles = [];

fileInput.addEventListener("change", function() {
  const files = Array.from(this.files);
  
  // add new files to selectedFiles
  selectedFiles = files;

  renderPreview();
});

function renderPreview() {
  preview.innerHTML = "";

  selectedFiles.forEach((file, index) => {
    const container = document.createElement("div");
    container.style.position = "relative";
    container.style.display = "inline-block";

    const img = document.createElement("img");
    img.src = URL.createObjectURL(file);
    img.style.maxWidth = "120px";
    img.style.maxHeight = "120px";
    img.style.borderRadius = "8px";
    img.style.border = "1px solid #555";

    // Delete button
  const delBtn = document.createElement("button");
delBtn.innerHTML = "×";
delBtn.classList.add("delete-btn"); // add this
delBtn.onclick = () => {
  selectedFiles.splice(index, 1);
  renderPreview();
};

    delBtn.style.position = "absolute";
    delBtn.style.top = "-8px";
    delBtn.style.right = "-8px";
    delBtn.style.background = "red";
    delBtn.style.color = "white";
    delBtn.style.border = "none";
    delBtn.style.borderRadius = "50%";
    delBtn.style.width = "20px";
    delBtn.style.height = "20px";
    delBtn.style.cursor = "pointer";
    delBtn.onclick = () => {
      selectedFiles.splice(index, 1);
      renderPreview();
    };

    container.appendChild(img);
    container.appendChild(delBtn);
    preview.appendChild(container);
  });
}

// Adjust upload form to use selectedFiles
document.getElementById("uploadForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  if (selectedFiles.length === 0) return alert("Please select images!");

  const formData = new FormData();
  selectedFiles.forEach(file => formData.append("images[]", file));

  // continue your fetch/upload logic
});

}


  // === Upload Form ===
  const uploadForm = document.getElementById("uploadForm");
  if(uploadForm){
    uploadForm.addEventListener("submit", async function(e) {
      e.preventDefault();
      // your existing fetch/upload logic here...
    });
  }

  // === Tab Switching ===
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".tab-panel").forEach(p => p.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(btn.dataset.tab).classList.add("active");
    });
  });

});
</script>

</body>

</html>




