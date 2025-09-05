<?php
session_start();

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email === "adminaramaru@gmail.com" && $password === "dev12345") {
        $_SESSION['admin'] = $email;
        $_SESSION['last_activity'] = time(); // catat waktu login
        header("Location: admin-view.php");
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Admin</title>
    <link rel="stylesheet" href="Assets/loginStyle.css">
</head>
<body>
    
    <div class="navContainer">
        <div class="navTitle">
            <div>Sistem Absensi Pegawai</div>
        </div>
        <div class="rightNav">
            <div class="toDate" id="todayDate"></div>
            <a href="index.php" class="loginBtn"><img src="Assets/logout-img.png" alt="admin"></a>
        </div>
    </div>

    <?php if (isset($_GET['expired'])): ?>
        <p style="color:red;">⚠️ Sesi Anda telah berakhir, silakan login ulang.</p>
    <?php endif; ?>

    <div class="loginContainer">

        <div class="welcomeContainer">
            <div class="welcomeText">
                <p class="w1">Hi, <br></p>
                <p class="w2">Selamat Datang!</p>
            </div>
            <img src="Assets/bg-login.png" alt="">
        </div>

        <div class="formContainer">
            <div class="formTitle">
                <h1>Login</h1>
                <p>Selamat Datang di Absensi Pegawai Aramaru Indah Global! <br/> Silakan masuk ke dalam dashboard admin dengan menggunakan username dan password admin!</p>
            </div>
            <form method="post" class="loginForm">
                <h3>Username</h3>
                <input type="email" name="email" placeholder="Masukkan email Anda" required><br>
                <h3>Password</h3>
                <input type="password" name="password" placeholder="Masukkann password Anda" required><br><br>
            <button type="submit">Login</button>
            </form>
            
        </div>

    </div>

</div>

    <p style="color:red;"><?= $error ?></p>

    <script src="function.js"></script>
</body>
</html>
