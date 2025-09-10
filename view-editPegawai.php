<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: view-admin.php"); exit; }
include "config/db_connect.php";
include "Assets/phpqrcode/qrlib.php"; // library QR Code

if (!isset($_GET['id'])) die("ID tidak ditemukan");
$id = $_GET['id'];

// ambil data lama
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) die("Data pegawai tidak ditemukan.");
$row = $result->fetch_assoc();
$stmt->close();

// update jika submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];

    $fotoPath = $row['foto']; // default: foto lama
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        $allowedTypes = ["jpg","jpeg","png","gif"];
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
                // hapus foto lama jika ada
                if ($row['foto'] && file_exists($row['foto'])) {
                    unlink($row['foto']);
                }
                $fotoPath = $targetFilePath;
            }
        }
    }

    // Generate ulang QR Code
    $qrData = $nama . " | " . $id;
    $qrDir = "uploads/qrcode/";
    if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

    $qrFile = $qrDir . "qr_" . $id . ".png";

    // hapus QR lama jika ada
    if ($row['qrPegawai'] && file_exists($row['qrPegawai'])) {
        unlink($row['qrPegawai']);
    }

    QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 5);

    // update DB
    $stmt = $conn->prepare("UPDATE pegawai SET nama=?, jabatan=?, foto=?, qrPegawai=? WHERE id=?");
    $stmt->bind_param("sssss", $nama, $jabatan, $fotoPath, $qrFile, $id);
    if ($stmt->execute()) {
        header("Location: view-detailPegawai.php?id=" . urlencode($id) . "&updated=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Pegawai</title>
  <link rel="stylesheet" href="Assets/css/style.css">
  <link rel="stylesheet" href="Assets/css/adminStyle.css">
  <style>
    .form-container {
      max-width: 500px;
      margin: 30px auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 12px;
      background: #f9f9f9;
    }
    .form-container h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    .form-container label {
      font-weight: bold;
    }
    .form-container input, .form-container select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      margin-bottom: 15px;
      border: 1px solid #bbb;
      border-radius: 6px;
    }
    .form-container button {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .btn-save {
      background: orange;
      color: white;
    }
    .btn-cancel {
      background: #ccc;
      margin-left: 5px;
    }
    .preview {
      margin-bottom: 15px;
      text-align: center;
    }
    .preview img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
    }
  </style>
</head>
<body>

<div class="navContainer">
  <div class="navTitle">Edit Pegawai</div>
</div>

<div class="form-container">
  <h2>Edit Data Pegawai</h2>
  <form method="post" enctype="multipart/form-data">
    <label>Nama:</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($row['nama']) ?>" required>

    <label>Jabatan:</label>
    <input type="text" name="jabatan" value="<?= htmlspecialchars($row['jabatan']) ?>" required>

    <label>Foto (kosongkan jika tidak ingin ganti):</label>
    <input type="file" name="foto" accept="image/*">
    <div class="preview">
      <p>Foto Lama:</p>
      <img src="<?= $row['foto'] ?>" alt="Foto Pegawai">
    </div>

    <button type="submit" class="btn-save">💾 Simpan</button>
    <a href="view-detailPegawai.php?id=<?= urlencode($row['id']) ?>"><button type="button" class="btn-cancel">Batal</button></a>
  </form>
</div>

</body>
</html>
