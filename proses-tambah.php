<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include "config/db_connect.php";
include "Assets/phpqrcode/qrlib.php"; // library QR Code

$nama = $_POST['nama'];

$kodeJabatan = $_POST['jabatan']; // 00, 01, 02, 99

$mapJabatan = [
    "00" => "Developer",
    "01" => "Owner",
    "02" => "Karyawan",
    "99" => "Lainnya"
];

$namaJabatan = isset($mapJabatan[$kodeJabatan]) ? $mapJabatan[$kodeJabatan] : "Tidak Diketahui";


// Ambil tanggal sekarang dengan format ddmm
$now = date("dm"); // contoh: 1009 untuk 10 September

// Cari urutan terakhir di tanggal ini
$sql = "SELECT id FROM pegawai 
        WHERE id LIKE 'AIG{$kodeJabatan}{$now}%' 
        ORDER BY id DESC LIMIT 1";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    $lastId = $res->fetch_assoc()['id'];
    $lastNumber = intval(substr($lastId, -3)); // ambil 3 digit terakhir
    $newNumber = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
} else {
    $newNumber = "001";
}

// Buat ID baru
$id = "AIG" . $kodeJabatan . $now . $newNumber;

// Upload foto
$targetDir = "uploads/";
if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

$fileName = time() . "_" . basename($_FILES["foto"]["name"]);
$targetFilePath = $targetDir . $fileName;

if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
    // Generate QR Code
    $qrData = $nama . " | " . $id;
    $qrDir = "uploads/qrcode/";
    if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

    $qrFile = $qrDir . "qr_" . $id . ".png";
    QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 5);

    // Simpan data ke database
    $sql = "INSERT INTO pegawai (id, nama, jabatan, foto, qrPegawai) 
            VALUES ('$id', '$nama', '$namaJabatan', '$targetFilePath', '$qrFile')";
    if ($conn->query($sql) === TRUE) {
        header("Location: view-admin.php");
        exit;
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "❌ Gagal upload foto.";
}
?>
