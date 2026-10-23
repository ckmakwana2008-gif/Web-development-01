<?php
require_once "db.php";
require_once "rbac_logger.php";

checkRole(['ADMIN']);

logAuditAction($pdo, "DASHBOARD_VIEW", "Admin accessed main summary dashboard.");

try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalRegistrations = $pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();

    $recentRegsQuery = "
        SELECT r.registration_id, s.full_name AS student_name, e.title AS event_title, r.registered_at, r.status
        FROM event_registrations r
        JOIN students s ON r.student_id = s.student_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.registered_at DESC LIMIT 5";
    $recentRegistrations = $pdo->query($recentRegsQuery)->fetchAll();

    $recentLogsQuery = "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 5";
    $recentAuditLogs = $pdo->query($recentLogsQuery)->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header style="background-color: #1e293b; color: white; padding: 1rem 2rem; display:flex; justify-content:space-between; align-items:center;">
  <div>
    <h2 style="margin:0; font-size:1.25rem;">StudentHub Admin</h2>
    <span style="font-size:0.8rem; color:#94a3b8;">Role: <strong><?php echo $_SESSION['role']; ?></strong> (<?php echo htmlspecialchars($_SESSION['username']); ?>)</span>
  </div>
  <nav>
    <?php renderDynamicMenu(); ?>
  </nav>
  <div>
    <a href="logout.php" class="btn btn-outline btn-sm" style="color:white; border-color:white;">Logout</a>
  </div>
</header>

<main class="container" style="margin-top: 2rem;">
  <h1>System Overview & Metrics</h1>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
    <div class="panel" style="border-left: 5px solid #3b82f6; padding: 1.25rem;">
      <h3 style="margin:0; font-size:0.9rem; color:#64748b;">Total Users</h3>
      <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0; color:#1e293b;"><?php echo $totalUsers; ?></p>
    </div>

    <div class="panel" style="border-left: 5px solid #10b981; padding: 1.25rem;">
      <h3 style="margin:0; font-size:0.9rem; color:#64748b;">Total Students</h3>
      <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0; color:#1e293b;"><?php echo $totalStudents; ?></p>
    </div>

    <div class="panel" style="border-left: 5px solid #f59e0b; padding: 1.25rem;">
      <h3 style="margin:0; font-size:0.9rem; color:#64748b;">Total Events</h3>
      <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0; color:#1e293b;"><?php echo $totalEvents; ?></p>
    </div>

    <div class="panel" style="border-left: 5px solid #8b5cf6; padding: 1.25rem;">
      <h3 style="margin:0; font-size:0.9rem; color:#64748b;">Event Registrations</h3>
      <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0; color:#1e293b;"><?php echo $totalRegistrations; ?></p>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">

    <section class="panel">
      <h2>Recent Event Registrations</h2>
      <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:1rem;">
        <thead>
          <tr style="background:#f8fafc;">
            <th>Student</th>
            <th>Event</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentRegistrations)): ?>
            <?php foreach ($recentRegistrations as $reg): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($reg['student_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($reg['event_title']); ?></td>
                <td><?php echo date('M d, H:i', strtotime($reg['registered_at'])); ?></td>
                <td>
                  <span style="padding:0.2rem 0.5rem; border-radius:4px; font-size:0.75rem; font-weight:bold; background:#dcfce7; color:#15803d;">
                    <?php echo htmlspecialchars($reg['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">No recent registrations found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

    <section class="panel">
      <h2>System Audit Activity Log</h2>
      <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:1rem;">
        <thead>
          <tr style="background:#f8fafc;">
            <th>User (Role)</th>
            <th>Action</th>
            <th>IP Address</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentAuditLogs)): ?>
            <?php foreach ($recentAuditLogs as $log): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($log['username']); ?></strong> (<?php echo $log['user_role']; ?>)</td>
                <td><code><?php echo htmlspecialchars($log['action']); ?></code></td>
                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                <td><?php echo date('H:i:s', strtotime($log['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">No audit logs recorded yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

  </div>
</main>

</body>
</html>
