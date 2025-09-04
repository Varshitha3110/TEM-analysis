<style>
  
.sidebar {
    width: 250px;
    background: #1f2430; /* deeper black */
    color: #eee;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding-top: 0px !important;
    margin-top: 10 !important;
    border-right: 1px solid #222; /* subtle divider */
  }

  .brand {
    text-align: center;
    padding: 20px 10px;
    border-bottom: 1px solid #222;
    margin-top: -13px;
  }

  .brand-logo {
    font-size: 30px;
    font-weight: bold;
    color: #00e0b8;
    margin-bottom: 8px;
  }

  .brand-text .title {
    font-size: 17px;
    font-weight: 700;
    color: #fff;
  }

  .brand-text .subtitle {
    font-size: 13px;
    color: #888;
  }

  .nav-card {
    flex-grow: 1;
    padding: 10px;
    border-radius: 40px;
  
  }

  .nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .nav-item {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    color: #ddd;
  }

  .nav-item:hover {
    background: #1e1e1e;
    color: #00e0b8;
  }

  .nav-item.active {
    background: #00e0b8;
    color: #111;
    font-weight: 600;
  }

  .nav-item .icon {
    margin-right: 12px;
    font-size: 16px;
  }

  .submenu {
    margin-left: 28px;
    margin-top: 6px;
    display: none;
    padding-left: 6px;
    border-left: 1px solid #222;
  }

  .nav-item.has-submenu:hover .submenu {
    display: block;
  }

  .submenu-item {
    font-size: 13px;
    color: #aaa;
    display: block;
    margin: 5px 0;
    text-decoration: none;
    transition: color 0.2s ease;
  }

  .submenu-item:hover {
    color: #00e0b8;
  }

  .sidebar-footer {
    text-align: center;
    padding: 12px;
    font-size: 12px;
    color: #666;
    border-top: 1px solid #222;
  }
  img{
    border-radius: 9px;
  }
/* Only style 'Images Uploaded' and 'History' links to remove blue link style */
.nav-item:not(.active) > a {
  color: inherit;          /* inherit color from nav-item */
  text-decoration: none;   /* remove underline */
  display: flex;           /* align icon and label horizontally */
  align-items: center;
  width: 100%;             /* fill entire nav-item */
  cursor: pointer;         /* pointer on hover */
}

.nav-item:not(.active) > a:hover {
  color: inherit;          /* keep same color on hover */
  text-decoration: none;   /* no underline on hover */
}

/* Make anchor behave like normal nav text inside nav-item */
.nav-item > a.nav-link {
  color: inherit;          /* inherit text color from parent nav-item */
  text-decoration: none;   /* remove underline */
  display: flex;           /* align icon and label horizontally */
  align-items: center;
  width: 100%;             /* fill entire nav-item */
  cursor: pointer;         /* keep pointer on hover */
}

.nav-item > a.nav-link:hover {
  color: inherit;          /* keep same color on hover */
  text-decoration: none;   /* no underline on hover */
}

</style>

<aside class="sidebar1">
  <div class="brand">
    <div class="brand-logo"><img src="images/excl logo.jfif" alt="E" height="50px" width="50px" ></div>
    <div class="brand-text">
      <div class="title">Exclcloud</div>
      <div class="subtitle">Solutions</div>
    </div>
  </div>
<div class="sidebar">
  <!-- Nav -->
  <div class="nav-card">
    <nav class="nav">
     <ul><a href="index.php">
  <li class="nav-item active" data-key="filter">
  <a href="high_index.php" class="nav-link">
    <span class="icon"><i class="fas fa-microscope"></i></span>
    <span class="label" style="font-size: 15px">High Magnification</span>
  </a>
</li>


<li class="nav-item has-submenu">
  <span class="icon"><i class="fas fa-chart-simple"></i></span>
  <span class="label">Magnification ▸</span>
  <ul class="submenu">
    <li><a href="high_index.php" class="submenu-item"><i class="fas fa-microscope"></i> High Magnification</a></li>
    <li><a href="index.php" class="submenu-item"><i class="fas fa-microscope"></i> Low Magnification</a></li>
  </ul>
</li>

<li class="nav-item has-submenu">
  <span class="icon"><i class="fas fa-file-export"></i></span>
  <span class="label">Export ▸</span>
  <ul class="submenu">
    <li><a href="high_exported_csv.php" class="submenu-item"><i class="fas fa-file-csv"></i> Export CSV</a></li>
    <li><a href="high_exported_pdf.php" class="submenu-item"><i class="fas fa-file-pdf"></i> Export PDF</a></li>
  </ul>
</li>


<li class="nav-item">
  <a href="high_images_uploaded.php">
    <span class="icon"><i class="fas fa-image"></i></span>
    <span class="label">Images Uploaded</span>
  </a>
</li>

<li class="nav-item">
  <a href="high_parameters.php">
    <span class="icon"><i class="fas fa-cogs"></i></span>
    <span class="label">Parameters</span>
  </a>
</li>
  

<li class="nav-item">
  <a href="high_history.php">
    <span class="icon"><i class="fas fa-clock-rotate-left"></i></span>
    <span class="label">History</span>
  </a>
</li>

      </ul>
    </nav>
  </div>
</div>
  <div class="sidebar-footer">
    <small>v1.0 · Local Demo</small>
  </div>
</aside>
