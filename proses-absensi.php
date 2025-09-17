<?php
session_start();
header("Content-Type: application/json");

include "config/db_connect.php";

if (!isset($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "ID pegawai tidak ditemukan"]);
    exit;
}

$idPegawai = $_POST['id'];
$tanggal = date("Y-m-d");
$waktuSekarang = date("H:i:s");

// === Generate id_absensi ===
$prefix = date("dmy"); // contoh: 170925
$sqlLast = "SELECT id_absensi FROM absensi WHERE id_absensi LIKE '{$prefix}%' ORDER BY id_absensi DESC LIMIT 1";
$resLast = $conn->query($sqlLast);

if ($resLast && $resLast->num_rows > 0) {
    $lastId = $resLast->fetch_assoc()['id_absensi'];
    $lastNumber = intval(substr($lastId, -1)); // ambil digit terakhir (nomor urut)
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$id_absensi = $prefix . $newNumber;

// === Cek apakah pegawai sudah absen hari ini ===
$sqlCheck = "SELECT * FROM absensi WHERE id = ? AND tanggal = ?";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("ss", $idPegawai, $tanggal);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // --- Belum absen → buat record baru (check-in) ---
    $sqlInsert = "INSERT INTO absensi (id_absensi, id, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?, ?)";
    $status = "Hadir";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("sssss", $id_absensi, $idPegawai, $tanggal, $waktuSekarang, $status);

    if ($stmtInsert->execute()) {
        echo json_encode(["status" => "success", "message" => "✅ Check-in berhasil"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal simpan check-in: " . $conn->error]);
    }
    $stmtInsert->close();
} else {
    $row = $result->fetch_assoc();
    if (empty($row['waktu_keluar'])) {
        // --- Sudah check-in tapi belum check-out → update checkout ---
        $sqlUpdate = "UPDATE absensi SET waktu_keluar = ? WHERE id_absensi = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ss", $waktuSekarang, $row['id_absensi']);

        if ($stmtUpdate->execute()) {
            echo json_encode(["status" => "success", "message" => "✅ Check-out berhasil"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal simpan check-out: " . $conn->error]);
        }
        $stmtUpdate->close();
    } else {
        // --- Sudah check-in dan check-out hari ini ---
        echo json_encode(["status" => "warning", "message" => "⚠️ Anda sudah absen masuk & keluar hari ini"]);
    }
}

$stmt->close();
$conn->close();
