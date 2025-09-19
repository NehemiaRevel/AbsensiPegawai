<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Pegawai</title>
    <link rel="stylesheet" href="Assets/css/style.css">
</head>
<body>

    <div class="navContainer">
        <div class="navTitle">
            <div>Sistem Absensi Pegawai</div>
        </div>
        <div class="rightNav">
            <div class="toDate" id="todayDate"></div>
            <a href="view-login.php" class="loginBtn"><img src="Assets/icon/admin-profile.png" alt="admin"></a>
        </div>
    </div>

    <div class="absenContainer">
        <h2>Scan Barcode ID Card</h2>

        <!-- 🔹 Tambahan pilihan mode -->
        <div class="modeContainer">
            <label>
                <input type="radio" name="mode" value="checkin" checked> Check-in
            </label>
            <label>
                <input type="radio" name="mode" value="checkout"> Check-out
            </label>
        </div>

        <div class="absenBox">
            <div class="camContainer">
                <video id="video" autoplay playsinline></video><br>
                <button id="toggleBtn" class="off">SCAN SEKARANG</button>
            </div>

            <div class="dataPegawaiContainer">
                <h2 id="titlePegawai">IDENTITAS PEGAWAI</h2>
                <div class="descPegawaiBox">
                    <div class="fotoPegawaiBox"></div>
                    <div class="dataPegawaiBox">
                        <p id="idPegawai">ID</p>
                        <p id="namaPegawai">Nama pegawai</p>
                        <p id="tanggalAbsen">Tanggal</p>
                        <p id="waktuAbsen">Waktu</p>
                        <p id="statusAbsen">Status</p>
                    </div>
                </div>
                <div id="status">Waiting for QR Code...</div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
    <script src="Assets/js/function.js"></script>
</body>
</html>
