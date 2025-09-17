<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include "config/db_connect.php";

$filterDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_absensi.xls");

echo "<table border='1'>";
echo "<tr>
        <th>ID Absen</th>
        <th>ID Pegawai</th>
        <th>Nama Pegawai</th>
        <th>Tanggal</th>
        <th>Waktu Masuk</th>
        <th>Waktu Keluar</th>
        <th>Status</th>
      </tr>";

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

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['id_absensi']}</td>
            <td>{$row['id']}</td>
            <td>{$row['nama']}</td>
            <td>{$row['tanggal']}</td>
            <td>".($row['waktu_masuk'] ?: "-")."</td>
            <td>".($row['waktu_keluar'] ?: "-")."</td>
            <td>{$row['status']}</td>
          </tr>";
}
echo "</table>";
?>
