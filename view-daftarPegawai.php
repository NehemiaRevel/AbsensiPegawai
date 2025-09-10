<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include "config/db_connect.php";

// --- Pagination ---
$limit = 5; // jumlah pegawai per halaman
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daftar Pegawai</title>
  <link rel="stylesheet" href="Assets/css/style.css">
  <link rel="stylesheet" href="Assets/css/adminStyle.css">
</head>
<body>
  <div class="navContainer">
    <div class="navTitle">Daftar Pegawai</div>
    <div class="rightNav">
      <div class="toDate" id="todayDate"></div>
      <a href="view-admin.php" class="backBtn"><img src="Assets/icon/back-btn.png" alt="back"></a>
    </div>
  </div>

  <div class="daftarContainerLengkap">
    <div class="titleDaftarPegawai">
      <h2>Semua Pegawai</h2>
      <a href="view-admin.php">Kembali</a>
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

    <!-- Pagination -->
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>">&laquo; Prev</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>">Next &raquo;</a>
      <?php endif; ?>
    </div>
  </div>

  <script src="Assets/js/function.js"></script>
</body>
</html>
