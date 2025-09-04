<header class="topbar">
  <div class="left">
    <!-- <button class="menu-toggle" id="menuToggle">☰</button> -->

    <div class="search">
      <input 
        id="searchInput" 
        type="search" 
        placeholder="Search sample, ID, date..." 
        autocomplete="off"
      />
      <div id="suggestions" class="suggestions"></div>
    </div>
  </div>

  <div class="right">
    <button class="cta">Hasetri</button>
    <div class="profile" id="profileBtn" style="margin-top: 15px;">
      <img src="images/hasetri logo.png" alt="profile" >
     
    </div>
  </div>
</header>

<script>
const searchInput = document.getElementById("searchInput");
const suggestionsBox = document.getElementById("suggestions");

searchInput.addEventListener("keyup", async function() {
  let value = this.value.trim();
  if (!value) {
    suggestionsBox.style.display = "none";
    return;
  }

  const res = await fetch(`high_search.php?q=${encodeURIComponent(value)}`);
  const matches = await res.json();

  if (matches.length > 0) {
    suggestionsBox.innerHTML = matches.map(m => `<div>${m}</div>`).join("");
    suggestionsBox.style.display = "block";
  } else {
    suggestionsBox.style.display = "none";
  }
});

// Click suggestion → move that file’s block to top
suggestionsBox.addEventListener("click", function(e) {
  if (e.target.tagName === "DIV") {
    const selectedFile = e.target.innerText;
    searchInput.value = selectedFile;
    suggestionsBox.style.display = "none";

    // Find file section in exported_csv.php
    const fileBlocks = document.querySelectorAll(".card");
    fileBlocks.forEach(block => {
      const title = block.querySelector(".excel-title");
      if (title && title.textContent.trim() === selectedFile) {
        // Move it to top of container
        const container = document.getElementById("excelTableContainer");
        container.prepend(block);
        block.scrollIntoView({ behavior: "smooth", block: "center" });
        block.style.background = "#00e0b820";
        setTimeout(() => block.style.background = "", 2000);
      }
    });
  }
});
</script>
