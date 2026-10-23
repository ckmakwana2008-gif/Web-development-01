<?php
require_once "auth_check.php";
requireAuth('student'); // Restrict access strictly to students
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard - StudentHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container">
    <a href="#" class="brand">StudentHub</a>
    <nav class="primary-nav">
      <ul>
        <li><a href="student_dashboard.php" aria-current="page">Dashboard</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="logout.php" class="btn btn-outline btn-sm">Logout</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
  <p>Role: <span class="badge badge-category"><?php echo htmlspecialchars($_SESSION['role']); ?></span></p>

  <div class="dashboard-metrics" style="margin-top:2rem;">
    <div class="metric-card">
      <h4>Enrolled Events</h4>
      <p>3</p>
    </div>
    <div class="metric-card">
      <h4>Pending Support Queries</h4>
      <p>1</p>
    </div>
  </div>
</main>

</body>
</html>
