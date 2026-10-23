<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function checkRole(array $allowedRoles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: login.php?error=" . urlencode("Authentication required."));
        exit();
    }

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        header("Location: unauthorized.php");
        exit();
    }
}

function logAuditAction(PDO $pdo, string $action, string $details = ''): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $userId   = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'UNKNOWN';
    $role     = $_SESSION['role'] ?? 'GUEST';
    $ip       = $_SERVER['REMOTE_ADDR'] === '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO audit_logs (user_id, username, user_role, action, details, ip_address) 
            VALUES (:user_id, :username, :role, :action, :details, :ip)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':user_id'  => $userId,
        ':username' => $username,
        ':role'     => $role,
        ':action'   => $action,
        ':details'  => $details,
        ':ip'       => $ip
    ]);
}

function renderDynamicMenu() {
    $role = $_SESSION['role'] ?? 'GUEST';

    $menuItems = [
        'ADMIN' => [
            ['label' => 'Dashboard', 'link' => 'admin_dashboard.php'],
            ['label' => 'Manage Students', 'link' => 'students_admin.php'],
            ['label' => 'Manage Events', 'link' => 'events_admin.php'],
            ['label' => 'System Audit Logs', 'link' => 'audit_logs.php'],
        ],
        'FACULTY' => [
            ['label' => 'Faculty Portal', 'link' => 'faculty_dashboard.php'],
            ['label' => 'View Events', 'link' => 'events_list.php'],
            ['label' => 'Student Records', 'link' => 'students_view.php'],
        ],
        'STUDENT' => [
            ['label' => 'My Profile', 'link' => 'student_profile.php'],
            ['label' => 'Explore Events', 'link' => 'events_browse.php'],
            ['label' => 'My Registrations', 'link' => 'my_registrations.php'],
        ]
    ];

    $links = $menuItems[$role] ?? [];

    echo '<ul class="nav-menu" style="display:flex; list-style:none; gap:1.5rem; margin:0; padding:0;">';
    foreach ($links as $item) {
        $active = (basename($_SERVER['PHP_SELF']) === $item['link']) ? 'font-weight:bold; text-decoration:underline;' : '';
        echo "<li><a href='{$item['link']}' style='color:white; text-decoration:none; {$active}'>{$item['label']}</a></li>";
    }
    echo '</ul>';
}
?>
