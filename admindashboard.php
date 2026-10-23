<?php
require_once "auth_check.php";
requireAuth('admin'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Management Portal - StudentHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container">
    <a href="#" class="brand">StudentHub Admin</a>
    <nav class="primary-nav">
      <ul>
        <li><a href="admin_dashboard.php" aria-current="page">Overview</a></li>
        <li><a href="logout.php" class="btn btn-outline btn-sm">Logout</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Administrative Control Panel</h1>
  <p>Administrator User: <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></p>

  <div class="panel" style="margin-top:2rem;">
    <h3>System Management Tools</h3>
    <p>Privileged features for event management and user access administration.</p>
  </div>
</main>

</body>
</html>
