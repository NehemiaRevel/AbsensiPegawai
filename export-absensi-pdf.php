<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include "config/db_connect.php";
require("Assets/fpdf/fpdf.php"); // pastikan sudah ada library FPDF

$filterDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";

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


$sql = "SELECT a.*, p.nama 
        FROM absensi a
        JOIN pegawai p ON a.id = p.id
        $whereClause
        ORDER BY a.tanggal DESC, a.waktu_masuk ASC";
$result = $conn->query($sql);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial", "B", 14);
$pdf->Cell(0, 10, "Laporan Absensi Pegawai", 0, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 10);
$pdf->Cell(30, 8, "ID Absen", 1);
$pdf->Cell(30, 8, "ID Pegawai", 1);
$pdf->Cell(40, 8, "Nama Pegawai", 1);
$pdf->Cell(25, 8, "Tanggal", 1);
$pdf->Cell(30, 8, "Masuk", 1);
$pdf->Cell(30, 8, "Keluar", 1);
$pdf->Cell(20, 8, "Status", 1);
$pdf->Ln();

$pdf->SetFont("Arial", "", 10);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(30, 8, $row['id_absensi'], 1);
    $pdf->Cell(30, 8, $row['id'], 1);
    $pdf->Cell(40, 8, $row['nama'], 1);
    $pdf->Cell(25, 8, $row['tanggal'], 1);
    $pdf->Cell(30, 8, $row['waktu_masuk'] ?: "-", 1);
    $pdf->Cell(30, 8, $row['waktu_keluar'] ?: "-", 1);
    $pdf->Cell(20, 8, $row['status'], 1);
    $pdf->Ln();
}

$pdf->Output("I", "Laporan_Absensi.pdf");
?>
