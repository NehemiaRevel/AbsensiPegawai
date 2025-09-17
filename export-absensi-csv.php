<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include "config/db_connect.php";

$filterDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";

// Nama file
$filename = "laporan_absensi.csv";

// Header untuk download CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename");

// Open output stream
$output = fopen("php://output", "w");

// Tulis header kolom
fputcsv($output, ["ID Absen", "ID Pegawai", "Nama Pegawai", "Tanggal", "Waktu Masuk", "Waktu Keluar", "Status"]);

$whereClause = "";
$where = [];

if (!empty($_GET['dari']) && !empty($_GET['sampai'])) {
    $dari = $conn->real_escape_string($_GET['dari']);
    $sampai = $conn->real_escape_string($_GET['sampai']);
    $where[] = "a.tanggal BETWEEN '$dari' AND '$sampai'";
}

if (!empty($_GET['pegawai'])) {
    $pegawaiId = $conn->real_escape_string($_GET['pegawai']);
    $where[] = "a.id = '$pegawaiId'";
}

$whereClause = "";
if (count($where) > 0) {
    $whereClause = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT a.*, p.nama 
        FROM absensi a
        JOIN pegawai p ON a.id = p.id
        $whereClause
        ORDER BY a.tanggal DESC, a.waktu_masuk ASC";
$result = $conn->query($sql);

// Tulis data baris demi baris
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id_absensi'],
        $row['id'],
        $row['nama'],
        $row['tanggal'],
        $row['waktu_masuk'] ?: "-",
        $row['waktu_keluar'] ?: "-",
        $row['status']
    ]);
}

fclose($output);
exit;
?>
