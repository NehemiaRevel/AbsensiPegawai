<?php
$host = "localhost";     // biasanya localhost di XAMPP
$user = "root";          // default user XAMPP
$pass = "";              // default password kosong
$db   = "aramaruindahglobal"; // nama database kamu

date_default_timezone_set('Asia/Jakarta');

$conn = new mysqli($host, $user, $pass, $db);

// cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
