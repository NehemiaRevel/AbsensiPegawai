<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include "config/db_connect.php";

// --- Pagination ---
$limit = 3; // jumlah pegawai per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Hitung total pegawai
$totalResult = $conn->query("SELECT COUNT(*) as total FROM pegawai");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Ambil data pegawai untuk halaman saat ini
$sql = "SELECT * FROM pegawai ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// --- Pagination Absensi ---
$limitAbsensi = 10; // jumlah data per halaman
$offsetAbsensi = ($page - 1) * $limitAbsensi;

$where = [];

// Filter tanggal
$filterDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : date("Y-m-d");

$whereClause = "";

if (!empty($_GET['dari']) && !empty($_GET['sampai'])) {
    $dari = $conn->real_escape_string($_GET['dari']);
    $sampai = $conn->real_escape_string($_GET['sampai']);
    $where[] = "a.tanggal BETWEEN '$dari' AND '$sampai'";
}

// Filter pegawai
if (!empty($_GET['pegawai'])) {
    $pegawaiId = $conn->real_escape_string($_GET['pegawai']);
    $where[] = "a.id = '$pegawaiId'";
}

// Susun klausa WHERE
$whereClause = "";
if (count($where) > 0) {
    $whereClause = "WHERE " . implode(" AND ", $where);
}

// Query utama
$sqlAbsensi = "SELECT a.*, p.nama 
        FROM absensi a
        JOIN pegawai p ON a.id = p.id
        $whereClause
        ORDER BY a.tanggal DESC, a.waktu_masuk ASC";

$resultAbsensi = $conn->query($sqlAbsensi);

// --- Hitung total data ---
$totalResultAbsensi = $conn->query("SELECT COUNT(*) as total FROM absensi a $whereClause");
$totalRowsAbsensi = $totalResultAbsensi->fetch_assoc()['total'];
$totalPagesAbsensi = ceil($totalRowsAbsensi / $limitAbsensi);

// --- Ambil data absensi ---
$sqlAbsensi = "SELECT a.*, p.nama 
        FROM absensi a
        JOIN pegawai p ON a.id = p.id
        $whereClause
        ORDER BY a.tanggal DESC, a.waktu_masuk ASC
        LIMIT $limitAbsensi OFFSET $offsetAbsensi";
$resultAbsensi = $conn->query($sqlAbsensi);

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="Assets/css/style.css">
  <link rel="stylesheet" href="Assets/css/adminStyle.css">
</head>
<body>

  <div class="navContainer">
    <div class="navTitle">
      <div>Dashboard Admin</div>
    </div>
    <div class="rightNav">
      <div class="toDate" id="todayDate"></div>
      <a href="index.php" class="logoutBtn"><img src="Assets/icon/logout-img.png" alt="admin"></a>
    </div>
  </div>

  <!-- Alert -->
  <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-danger">🗑 Pegawai berhasil dihapus.</div>
  <?php endif; ?>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success">✅ Pegawai baru berhasil ditambahkan.</div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">❌ Terjadi kesalahan: <?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <!-- Data Absensi Pegawai -->
   <div class="containerDataAbsensi">
  <div class="toolbar">
  <!-- Filter Tanggal & Pegawai -->
  <form method="get" action="" class="filter-form">
    <label for="dari">Dari:</label>
    <input type="date" id="dari" name="dari" value="<?= isset($_GET['dari']) ? htmlspecialchars($_GET['dari']) : '' ?>">

    <label for="sampai">Sampai:</label>
    <input type="date" id="sampai" name="sampai" value="<?= isset($_GET['sampai']) ? htmlspecialchars($_GET['sampai']) : '' ?>">

    <label for="pegawai">Pegawai:</label>
    <select id="pegawai" name="pegawai">
      <option value="">-- Semua Pegawai --</option>
      <?php
      $pegawaiResult = $conn->query("SELECT id, nama FROM pegawai ORDER BY nama ASC");
      while ($p = $pegawaiResult->fetch_assoc()) {
          $selected = (isset($_GET['pegawai']) && $_GET['pegawai'] == $p['id']) ? "selected" : "";
          echo "<option value='{$p['id']}' $selected>{$p['nama']} ({$p['id']})</option>";
      }
      ?>
    </select>

    <button type="submit" class="btnFilter">Filter</button>
    <a href="view-admin.php" class="btnReset">Reset</a>
  </form>

  <!-- Dropdown Export -->
  <div class="dropdown">
    <button class="dropbtn">⬇️ Export Laporan</button>
    <div class="dropdown-content">
      <!-- Excel -->
      <a href="export-absensi-excel.php?dari=<?= $_GET['dari'] ?? '' ?>&sampai=<?= $_GET['sampai'] ?? '' ?>&pegawai=<?= $_GET['pegawai'] ?? '' ?>" target="_blank">📊 Excel (Range)</a>
      <a href="export-absensi-excel.php" target="_blank">📊 Excel (Semua)</a>

      <!-- PDF -->
      <a href="export-absensi-pdf.php?dari=<?= $_GET['dari'] ?? '' ?>&sampai=<?= $_GET['sampai'] ?? '' ?>&pegawai=<?= $_GET['pegawai'] ?? '' ?>" target="_blank">📄 PDF (Range)</a>
      <a href="export-absensi-pdf.php" target="_blank">📄 PDF (Semua)</a>

      <!-- CSV -->
      <a href="export-absensi-csv.php?dari=<?= $_GET['dari'] ?? '' ?>&sampai=<?= $_GET['sampai'] ?? '' ?>&pegawai=<?= $_GET['pegawai'] ?? '' ?>" target="_blank">🗂 CSV (Range)</a>
      <a href="export-absensi-csv.php" target="_blank">🗂 CSV (Semua)</a>
    </div>
  </div>
</div>

  <table class="absensiTable">
    <tr>
      <th>ID Absen</th>
      <th>ID Pegawai</th>
      <th>Nama Pegawai</th>
      <th>Tanggal</th>
      <th>Waktu Masuk</th>
      <th>Waktu Keluar</th>
      <th>Status</th>
    </tr>
    <?php if ($resultAbsensi->num_rows > 0): ?>
      <?php while($row = $resultAbsensi->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['id_absensi']) ?></td>
          <td><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
          <td><?= $row['waktu_masuk'] ? htmlspecialchars($row['waktu_masuk']) : "-" ?></td>
          <td><?= $row['waktu_keluar'] ? htmlspecialchars($row['waktu_keluar']) : "-" ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7">Tidak ada data absensi.</td></tr>
    <?php endif; ?>
  </table>

  <!-- Pagination -->
  <div class="paginationAbsensi">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page-1 ?>&tanggal=<?= $filterDate ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $max_links = 5;
    $start = max(1, $page - floor($max_links / 2));
    $end = min($totalPagesAbsensi, $start + $max_links - 1);

    if ($end - $start + 1 < $max_links) {
      $start = max(1, $end - $max_links + 1);
    }

    if ($start > 1) {
      echo '<a href="?page=1&tanggal='.$filterDate.'">1</a>';
      if ($start > 2) echo '<span>...</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
      echo '<a href="?page='.$i.'&tanggal='.$filterDate.'" class="'.($i==$page?'active':'').'">'.$i.'</a>';
    }

    if ($end < $totalPagesAbsensi) {
      if ($end < $totalPagesAbsensi - 1) echo '<span>...</span>';
      echo '<a href="?page='.$totalPagesAbsensi.'&tanggal='.$filterDate.'">'.$totalPagesAbsensi.'</a>';
    }
    ?>

    <?php if ($page < $totalPagesAbsensi): ?>
      <a href="?page=<?= $page+1 ?>&tanggal=<?= $filterDate ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
  </div>

  <!-- Container Daftar Pegawai -->
  <div class="daftarContainerLengkap">
    <div class="titleDaftarPegawai">
      <h2>Daftar Pegawai</h2>
    </div>
    <?php if ($result->num_rows > 0) { ?>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="daftarBox">
          <div class="daftarLeft">
            <div class="profilPegawai">
              <img class="displayPegawai" src="<?= $row['foto'] ?>" alt="Foto <?= htmlspecialchars($row['nama']) ?>" >
            </div>
            <div class="descPegawai">
              <div class="namaPegawai"><?= htmlspecialchars($row['nama']) ?></div>
              <div class="idPegawai"><?= htmlspecialchars($row['id']) ?></div>
            </div>
          </div>
          <div class="daftarRight">
            <a href="view-detailPegawai.php?id=<?= urlencode($row['id']) ?>">
              <button class="detailBtn">Detail</button>
            </a>
          </div>
        </div>
      <?php } ?>
    <?php } else { ?>
      <p>Belum ada pegawai.</p>
    <?php } ?>

    <div class="daftarPegawaiBtn">

      <!-- Pagination -->
<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?>">&laquo; Prev</a>
  <?php endif; ?>

  <?php
  $max_links = 3; // maksimal 5 nomor halaman
  $start = max(1, $page - floor($max_links / 2));
  $end = min($totalPages, $start + $max_links - 1);

  if ($end - $start + 1 < $max_links) {
      $start = max(1, $end - $max_links + 1);
  }

  if ($start > 1) {
      echo '<a href="?page=1">1</a>';
      if ($start > 2) echo '<span>...</span>';
  }

  for ($i = $start; $i <= $end; $i++): ?>
    <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor;

  if ($end < $totalPages) {
      if ($end < $totalPages - 1) echo '<span>...</span>';
      echo '<a href="?page=' . $totalPages . '">' . $totalPages . '</a>';
  }
  ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page+1 ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

      <!-- Tombol tambah pegawai -->
    <div class="tambahPegawaiBtn">
      <p>+ Tambah Pegawai</p>
    </div>
    </div>

  </div>

  <!-- Modal Form Tambah -->
  <div id="pegawaiModal" class="modal">
    <div class="modal-content">
      <span class="closeBtn">&times;</span>
      <h2>Tambah Pegawai</h2>
      <form method="post" enctype="multipart/form-data" action="proses-tambah.php">
        <label>Nama:</label>
        <input type="text" name="nama" required>

        <label>Jabatan:</label>
        <select name="jabatan" required>
          <option value="00">Developer</option>
          <option value="01">Owner</option>
          <option value="02">Karyawan</option>
          <option value="99">Lainnya</option>
        </select>

        <label>Foto:</label>
        <input type="file" name="foto" accept="image/*" required>

        <button type="submit">💾 Simpan</button>
      </form>
    </div>
  </div>

  <!-- JS Modal + tanggal + auto hide alert -->
  <script>
    const modal = document.getElementById("pegawaiModal");
    const btn = document.querySelector(".tambahPegawaiBtn");
    const span = document.querySelector(".closeBtn");

    btn.onclick = () => modal.style.display = "block";
    span.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; }

    // auto hide alert
    setTimeout(() => {
      document.querySelectorAll(".alert").forEach(el => {
        el.style.transition = "opacity 0.5s ease";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
      });
    }, 3000);
  </script>
  <script src="Assets/js/function.js"></script>
</body>
</html>
