<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include "config/db_connect.php";

// ambil id dari URL
if (!isset($_GET['id'])) {
    die("Pegawai tidak ditemukan.");
}
$id = $_GET['id'];

// ambil data pegawai
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Data pegawai tidak ditemukan.");
}
$row = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Pegawai</title>
  <link rel="stylesheet" href="Assets/css/style.css">
  <link rel="stylesheet" href="Assets/css/detailStyle.css">
  <style>
    .detail-card {
      max-width: 600px;
      margin: 30px auto;
      padding: 25px;
      border: 1px solid #ccc;
      border-radius: 12px;
      background: #f9f9f9;
      text-align: center;
    }
    .detail-card h2 {
      margin-bottom: 15px;
    }
    .detail-card img.foto {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 15px;
      border: 3px solid #ddd;
    }
    .detail-info {
      text-align: left;
      margin: 15px auto;
      max-width: 400px;
    }
    .detail-info p {
      margin: 6px 0;
    }
    .qr-section {
      margin: 20px 0;
    }
    .qr-section img {
      width: 160px;
      height: 160px;
    }
    .action-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .action-buttons button {
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }
    .btn-download { background: green; color: white; }
    .btn-edit { background: orange; color: white; }
    .btn-delete { background: red; color: white; }
    .alert {
      max-width: 600px;
      margin: 15px auto;
      padding: 12px;
      border-radius: 6px;
      text-align: center;
      font-weight: bold;
    }
    .alert-success { background: #ddffdd; color: #060; border: 1px solid #060; }
  </style>
</head>
<body>
  <div class="navContainer">
    <div class="navTitle">Detail Pegawai</div>
    <div class="rightNav">
      <div class="toDate" id="todayDate"></div>
      <a href="view-admin.php" class="backBtn"><img src="Assets/icon/back-btn.png" alt="back"></a>
    </div>
  </div>

  <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">✏️ Data pegawai berhasil diperbarui.</div>
  <?php endif; ?>

  <div class="detail-card">
    <h2><?= htmlspecialchars($row['nama']) ?></h2>
    <img class="displayPegawai" src="<?= $row['foto'] ?>" alt="Foto Pegawai">

    <div class="detail-info">
      <p><b>ID Pegawai:</b> <?= htmlspecialchars($row['id']) ?></p>
      <p><b>Jabatan:</b> <?= htmlspecialchars($row['jabatan']) ?></p>
      <p><b>Tanggal Input:</b> <?= date("d M Y H:i", strtotime($row['created_at'])) ?></p>
    </div>

    <div class="qr-section">
      <h3>QR Code</h3>
      <img src="<?= $row['qrPegawai'] ?>" alt="QR Pegawai"><br>
    </div>

    <div class="action-buttons">
      <a href="<?= $row['qrPegawai'] ?>" download="QR_<?= $row['id'] ?>.png">
        <button class="btn-download">⬇️ Download QR</button>
      </a>
      <a href="view-editPegawai.php?id=<?= urlencode($row['id']) ?>">
        <button class="btn-edit">✏️ Edit Data</button>
      </a>
      <a href="hapus-pegawai.php?id=<?= urlencode($row['id']) ?>" onclick="return confirm('Yakin hapus pegawai ini?');">
        <button class="btn-delete">🗑 Hapus</button>
      </a>
    </div>
  </div>

  <script src="Assets/js/function.js"></script>
  <script>
    // auto hide alert
    setTimeout(() => {
      document.querySelectorAll(".alert").forEach(el => {
        el.style.transition = "opacity 0.5s ease";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
      });
    }, 3000);
  </script>
</body>
</html>
