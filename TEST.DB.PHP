<?php
/* ==========================================================================
   StudentHub Query Processor using Prepared Statements
   ========================================================================== */

require_once 'db.php';

// Example 1: Secure Registration Insertion using Prepared Statement
function registerStudentForEvent(PDO $pdo, int $studentId, int $eventId): bool {
    $sql = "INSERT INTO registrations (student_id, event_id, status) VALUES (:student_id, :event_id, 'CONFIRMED')";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Registration failed: " . $e->getMessage());
        return false;
    }
}

// Example 2: Fetch Student Registrations with Inner Join
function getStudentRegistrations(PDO $pdo, int $studentId): array {
    $sql = "SELECT s.full_name, s.email, e.title AS event_title, e.event_date, r.registration_date, r.status
            FROM registrations r
            JOIN students s ON r.student_id = s.student_id
            JOIN events e ON r.event_id = e.event_id
            WHERE s.student_id = :student_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':student_id' => $studentId]);
    return $stmt->fetchAll();
}

// Execution Test
$studentIdToTest = 1;
$registrations = getStudentRegistrations($pdo, $studentIdToTest);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Practical 08 - Database Test Output</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
  <h2>Student Registrations (PDO Output Test)</h2>
  
  <?php if (!empty($registrations)): ?>
    <p>Registrations for Student: <strong><?php echo htmlspecialchars($registrations[0]['full_name']); ?></strong> (<?php echo htmlspecialchars($registrations[0]['email']); ?>)</p>
    <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:1rem;">
      <thead>
        <tr style="background:#f1f5f9;">
          <th>Event Title</th>
          <th>Event Date</th>
          <th>Registration Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registrations as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['event_title']); ?></td>
            <td><?php echo htmlspecialchars($row['event_date']); ?></td>
            <td><?php echo htmlspecialchars($row['registration_date']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No event registrations found for this student.</p>
  <?php endif; ?>
</main>
</body>
</html>
