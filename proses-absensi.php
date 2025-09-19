<?php
session_start();
include "config/db_connect.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPegawai = $_POST['id'];
    $mode = $_POST['mode'] ?? 'checkin'; // default checkin
    $tanggal = date("Y-m-d");
    $waktu = date("H:i:s");

    // 🔹 Generate prefix ID berdasarkan tanggal (ddmmyy)
    $prefix = date("dmy");

    // Cek apakah pegawai sudah absen hari ini
    $cek = $conn->prepare("SELECT * FROM absensi WHERE id = ? AND tanggal = ?");
    $cek->bind_param("ss", $idPegawai, $tanggal);
    $cek->execute();
    $res = $cek->get_result();

    if ($res->num_rows == 0) {
        // Belum ada record untuk hari ini
        if ($mode === "checkin") {
            // Cari id_absensi terakhir hari ini
            $q = $conn->query("SELECT id_absensi FROM absensi WHERE id_absensi LIKE '{$prefix}%' ORDER BY id_absensi DESC LIMIT 1");
            if ($q->num_rows > 0) {
                $lastId = $q->fetch_assoc()['id_absensi'];
                $lastNum = intval(substr($lastId, 6)); // ambil nomor urut
                $newNum = $lastNum + 1;
            } else {
                $newNum = 1;
            }

            // Format id_absensi baru
            $id_absensi = $prefix . $newNum;

            // Insert check-in
            $stmt = $conn->prepare("INSERT INTO absensi (id_absensi, id, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?, 'Hadir')");
            $stmt->bind_param("ssss", $id_absensi, $idPegawai, $tanggal, $waktu);
            $stmt->execute();

            echo json_encode(["status" => "success", "message" => "Check-in berhasil"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Belum check-in, tidak bisa check-out"]);
        }
    } else {
        // Sudah ada record
        $row = $res->fetch_assoc();

        if ($mode === "checkin") {
            if ($row['waktu_masuk']) {
                echo json_encode(["status" => "warning", "message" => "Sudah check-in"]);
            } else {
                // update check-in jika belum ada
                $stmt = $conn->prepare("UPDATE absensi SET waktu_masuk=? WHERE id_absensi=?");
                $stmt->bind_param("ss", $waktu, $row['id_absensi']);
                $stmt->execute();
                echo json_encode(["status" => "success", "message" => "Check-in berhasil"]);
            }
        } else { // checkout
            if ($row['waktu_keluar']) {
                echo json_encode(["status" => "warning", "message" => "Sudah check-out"]);
            } else {
                $stmt = $conn->prepare("UPDATE absensi SET waktu_keluar=? WHERE id_absensi=?");
                $stmt->bind_param("ss", $waktu, $row['id_absensi']);
                $stmt->execute();
                echo json_encode(["status" => "success", "message" => "Check-out berhasil"]);
            }
        }
    }
}
?>
