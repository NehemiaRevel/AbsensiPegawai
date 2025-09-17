<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include "config/db_connect.php";

// --- Pagination ---
$limitAbsensi = 10; // jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
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
$totalResult = $conn->query("SELECT COUNT(*) as total FROM absensi a $whereClause");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limitAbsensi);

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
  <title>Laporan Absensi Pegawai</title>
  <link rel="stylesheet" href="Assets/css/style.css">
  <link rel="stylesheet" href="Assets/css/adminStyle.css">
  <style>
  </style>
</head>
<body>
  <div class="navContainer">
    <div class="navTitle">Laporan Absensi</div>
    <div class="rightNav">  
      <div class="toDate" id="todayDate"></div>
      <a href="view-admin.php" class="backBtn"><img src="Assets/icon/back-btn.png" alt="back"></a>
    </div>
  </div>

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
    <a href="view-absensi.php" class="btnReset">Reset</a>
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


  <table>
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
    $end = min($totalPages, $start + $max_links - 1);

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

    if ($end < $totalPages) {
      if ($end < $totalPages - 1) echo '<span>...</span>';
      echo '<a href="?page='.$totalPages.'&tanggal='.$filterDate.'">'.$totalPages.'</a>';
    }
    ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page+1 ?>&tanggal=<?= $filterDate ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>

  <script src="Assets/js/function.js"></script>
</body>
</html>
