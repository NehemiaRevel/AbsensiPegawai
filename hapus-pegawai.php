<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include "config/db_connect.php";

if (!isset($_GET['id'])) die("ID tidak ditemukan");
$id = $_GET['id'];

// ambil data lama untuk hapus file
$stmt = $conn->prepare("SELECT foto, qrPegawai FROM pegawai WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) die("Data pegawai tidak ditemukan.");
$row = $result->fetch_assoc();
$stmt->close();

// hapus foto & qr lama jika ada
if ($row['foto'] && file_exists($row['foto'])) unlink($row['foto']);
if ($row['qrPegawai'] && file_exists($row['qrPegawai'])) unlink($row['qrPegawai']);

// hapus pegawai dari DB
$stmt = $conn->prepare("DELETE FROM pegawai WHERE id=?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
    header("Location: view-admin.php?deleted=1");
    exit;
} else {
    header("Location: view-admin.php?error=" . urlencode($conn->error));
    exit;
}
