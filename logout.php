<?php
session_start();
$_SESSION = [];              // kosongkan semua session
session_unset();             // hapus variabel session
session_destroy();           // hancurkan session

// hapus cookie session juga
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: login.php");
exit;
?>
