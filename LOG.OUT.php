<?php
require_once "db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_COOKIE['remember_token'])) {
    $tokenHash = hash('sha256', $_COOKIE['remember_token']);
    $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token_hash = :thash");
    $stmt->execute([':thash' => $tokenHash]);

    setcookie('remember_token', '', time() - 3600, '/');
}
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php?logout=success");
exit();
?>
