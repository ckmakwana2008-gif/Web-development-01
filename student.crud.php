<?php
/* ==========================================================================
   Student Management Subsystem - CRUD, Search, and Filter
   Course Code: ITUE203 - Practical 11
   ========================================================================== */
require_once "db_mysqli.php"; // Centralized MySQLi connection

$message = "";
$messageType = "";

// --------------------------------------------------------------------------
// 1. HANDLE POST ACTIONS (CREATE, UPDATE, DELETE)
// --------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // ACTION A: CREATE NEW STUDENT
    if ($action === 'create') {
        $fullName   = filter_var(trim($_POST['full_name']), FILTER_SANITIZE_SPECIAL_CHARS);
        $email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $phone      = filter_var(trim($_POST['phone']), FILTER_SANITIZE_SPECIAL_CHARS);
        $department = filter_var(trim($_POST['department']), FILTER_SANITIZE_SPECIAL_CHARS);

        if (!empty($fullName) && !empty($email) && !empty($phone) && !empty($department)) {
            $stmt = $conn->prepare("INSERT INTO students (full_name, email, phone, department) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullName, $email, $phone, $department);

            if ($stmt->execute()) {
                $message = "Student record created successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to create record: " . $conn->error;
                $messageType = "danger";
            }
            $stmt->close();
        } else {
            $message = "All fields are required for registration.";
            $messageType = "danger";
        }
    }

    // ACTION B: UPDATE EXISTING STUDENT
    elseif ($action === 'update') {
        $studentId  = intval($_POST['student_id']);
        $fullName   = filter_var(trim($_POST['full_name']), FILTER_SANITIZE_SPECIAL_CHARS);
        $email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $phone      = filter_var(trim($_POST['phone']), FILTER_SANITIZE_SPECIAL_CHARS);
        $department = filter_var(trim($_POST['department']), FILTER_SANITIZE_SPECIAL_CHARS);

        if ($studentId > 0 && !empty($fullName) && !empty($email)) {
            $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, department = ? WHERE student_id = ?");
            $stmt->bind_param("ssssi", $fullName, $email, $phone, $department, $studentId);

            if ($stmt->execute()) {
                $message = "Student record updated successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to update record: " . $conn->error;
                $messageType = "danger";
            }
            $stmt->close();
        }
    }

    // ACTION C: DELETE STUDENT
    elseif ($action === 'delete') {
        $studentId = intval($_POST['student_id']);

        if ($studentId > 0) {
            $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->bind_param("i", $studentId);

            if ($stmt->execute()) {
                $message = "Student record deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to delete record: " . $conn->error;
                $messageType = "danger";
            }
            $stmt->close();
        }
    }
}

// --------------------------------------------------------------------------
// 2. FETCH RECORDS WITH DYNAMIC SEARCH & FILTERING (READ)
// --------------------------------------------------------------------------
$search     = trim($_GET['search'] ?? '');
$filterDept = trim($_GET['department'] ?? '');

$query = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
    $searchParam = "%" . $search . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($filterDept)) {
    $query .= " AND department = ?";
    $params[] = $filterDept;
    $types .= "s";
}

$query .= " ORDER BY student_id DESC";

$stmtFetch = $conn->prepare($query);
if (!empty($params)) {
    $stmtFetch->bind_param($types, ...$params);
}
$stmtFetch->execute();
$resultStudents = $stmtFetch->get_result();

// Fetch record for editing if edit_id is present
$editStudent = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmtEdit = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmtEdit->bind_param("i", $editId);
    $stmtEdit->execute();
    $editStudent = $stmtEdit->get_result()->fetch_assoc();
    $stmtEdit->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Student Records Management</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function confirmDelete(studentName) {
      return confirm("Are you sure you want to delete student: " + studentName + "? This action cannot be undone.");
    }
  </script>
</head>
<body>

<main class="container">
  <h1>Student Management Directory</h1>

  <!-- UI Feedback Alert Message Banner -->
  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'info' : 'danger'; ?>" 
         style="padding:1rem; border-radius:8px; margin-bottom:1.5rem; 
                background-color: <?php echo $messageType === 'success' ? '#d1fae5' : '#fee2e2'; ?>; 
                color: <?php echo $messageType === 'success' ? '#065f46' : '#991b1b'; ?>;">
      <strong><?php echo $messageType === 'success' ? 'Success:' : 'Error:'; ?></strong> <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <!-- Add / Edit Student Form -->
  <section class="panel" style="margin-bottom: 2rem;">
    <h2><?php echo $editStudent ? "Edit Student Record" : "Add New Student"; ?></h2>
    <form action="students_crud.php" method="POST">
      <input type="hidden" name="action" value="<?php echo $editStudent ? "update" : "create"; ?>">
      <?php if ($editStudent): ?>
        <input type="hidden" name="student_id" value="<?php echo $editStudent['student_id']; ?>">
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($editStudent['full_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($editStudent['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($editStudent['phone'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="department">Department</label>
          <select id="department" name="department" required>
            <option value="">-- Select --</option>
            <?php 
              $depts = ["Computer Engineering", "Information Technology", "Electronics", "Mechanical", "Civil"];
              foreach ($depts as $d):
                $selected = ($editStudent && $editStudent['department'] === $d) ? 'selected' : '';
            ?>
              <option value="<?php echo $d; ?>" <?php echo $selected; ?>><?php echo $d; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="margin-top:1rem; display:flex; gap:1rem;">
        <button type="submit" class="btn btn-primary"><?php echo $editStudent ? "Update Student" : "Add Student"; ?></button>
        <?php if ($editStudent): ?>
          <a href="students_crud.php" class="btn btn-outline">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </section>

  <!-- Search and Filter Control Panel -->
  <section class="control-panel">
    <form action="students_crud.php" method="GET" style="display:contents;">
      <div class="control-group">
        <label for="search">Search Name/Email</label>
        <input type="text" id="search" name="search" placeholder="Type keyword..." value="<?php echo htmlspecialchars($search); ?>">
      </div>

      <div class="control-group">
        <label for="dept_filter">Filter by Department</label>
        <select id="dept_filter" name="department">
          <option value="">All Departments</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?php echo $d; ?>" <?php echo $filterDept === $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="control-group" style="justify-content: flex-end;">
        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
        <a href="students_crud.php" class="btn btn-outline btn-sm">Reset</a>
      </div>
    </form>
  </section>

  <!-- Students Data Table -->
  <section class="panel">
    <h2>Student Directory Listing (<?php echo $resultStudents->num_rows; ?> Records)</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:1rem;">
      <thead>
        <tr style="background-color: var(--bg-color, #f8fafc);">
          <th>ID</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Department</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resultStudents->num_rows > 0): ?>
          <?php while ($row = $resultStudents->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['student_id']; ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['phone']); ?></td>
              <td><span class="badge badge-category"><?php echo htmlspecialchars($row['department']); ?></span></td>
              <td>
                <div style="display:flex; gap:0.5rem;">
                  <a href="students_crud.php?edit_id=<?php echo $row['student_id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                  
                  <form action="students_crud.php" method="POST" onsubmit="return confirmDelete('<?php echo htmlspecialchars($row['full_name']); ?>');" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                    <button type="submit" class="btn btn-sm" style="background:#ef4444; color:white; border:none;">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:1.5rem;">No student records found matching the criteria.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
</main>

</body>
</html>
