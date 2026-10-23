<?php
/* ==========================================================================
   Secure Login Processing Engine with Password Verification & Tokens
   ========================================================================== */
require_once "db.php"; // Include PDO connection
require_once "auth_check.php";

$error = "";

// Redirect if already authenticated
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginInput = trim($_POST['username_or_email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $remember   = isset($_POST['remember_me']);

    if (empty($loginInput) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Fetch user record by username or email
        $stmt = $pdo->prepare("SELECT user_id, username, email, password_hash, role, full_name FROM users WHERE username = :input OR email = :input LIMIT 1");
        $stmt->execute([':input' => $loginInput]);
        $user = $stmt->fetch();

        // Verify user existence and password hash match
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // SECURITY: Regenerate session ID to block Session Fixation
            session_regenerate_id(true);

            // Populate Session State
            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['last_activity'] = time();

            // Handle Optional "Remember-Me" Token
            if ($remember) {
                $token = bin2hex(random_bytes(32)); // High-entropy random token
                $tokenHash = hash('sha256', $token);
                $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days valid

                // Save hashed token in database
                $stmtToken = $pdo->prepare("INSERT INTO user_tokens (user_id, token_hash, expires_at) VALUES (:uid, :thash, :expires)");
                $stmtToken->execute([
                    ':uid'     => $user['user_id'],
                    ':thash'   => $tokenHash,
                    ':expires' => $expiry
                ]);

                // Store raw token in HTTP-only cookie
                setcookie('remember_token', $token, [
                    'expires'  => time() + (86400 * 30),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            // Role-Based Redirection
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();

        } else {
            $error = "Invalid username/email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Portal Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="container form-container">
  <h1>Sign In to StudentHub</h1>

  <?php if (isset($_GET['timeout'])): ?>
    <div class="alert alert-info" style="background:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
      Your session expired due to inactivity. Please log in again.
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form action="login.php" method="POST" novalidate>
    <div class="form-group">
      <label for="username_or_email">Username or Email Address</label>
      <input type="text" id="username_or_email" name="username_or_email" required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group checkbox-group" style="margin-bottom:1.5rem;">
      <input type="checkbox" id="remember_me" name="remember_me">
      <label for="remember_me">Remember me for 30 days</label>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
  </form>
</main>

</body>
</html>
