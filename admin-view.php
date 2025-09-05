<?php
session_start();

// cek apakah sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// atur waktu kadaluarsa (3 detik)
$timeout = 3; // detik

// cek apakah sudah ada waktu login
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} else {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        // session kadaluarsa
        session_unset();
        session_destroy();
        header("Location: login.php?expired=1");
        exit;
    }
}

// perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Assets/css/style.css">
    <link rel="stylesheet" href="Assets/css/adminStyle.css">
</head>
<body>

    <div class="navContainer">
        <div class="navTitle">
            <div>Dashboard Admin</div>
        </div>
        <div class="rightNav">
            <div class="toDate" id="todayDate"></div>
            <a href="index.php" class="logoutBtn"><img src="Assets/logout-img.png" alt="admin"></a>
        </div>
    </div>

    <div class="daftarContainer">
        <h2>Daftar Pegawai</h2>
        <div class="daftarBox">
            <div class="daftarLeft">
                <div class="icon"></div>
                <div class="descPegawai">
                    <div class="namaPegawai">Nama Pegawai</div>
                    <div class="idPegawai">AIG010828001</div>
                </div>
            </div>
            <div class="daftarRight">
                <a href="#">Detail</a>
            </div>
        </div>
        <div class="daftarBox">
            <div class="daftarLeft">
                <div class="icon"></div>
                <div class="descPegawai">
                    <div class="namaPegawai">Nama Pegawai</div>
                    <div class="idPegawai">AIG010828001</div>
                </div>
            </div>
            <div class="daftarRight">
                <a href="#">Detail</a>
            </div>
        </div>
        <div class="daftarBox">
            <div class="daftarLeft">
                <div class="icon"></div>
                <div class="descPegawai">
                    <div class="namaPegawai">Nama Pegawai</div>
                    <div class="idPegawai">AIG010828001</div>
                </div>
            </div>
            <div class="daftarRight">
                <a href="#">Detail</a>
            </div>
        </div>
        <div class="tambahPegawaiBtn">
            <p>Tambah</p>
        </div>
    </div>
    
    <script src="Assets/js/function.js"></script>
</body>
</html>