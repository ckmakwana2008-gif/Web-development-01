<?php
/* ==========================================================================
   Event Management Subsystem with File Upload & Validation
   Course Code: ITUE203 - Practical 12
   ========================================================================== */
require_once "db.php"; // Centralized PDO database connection
require_once "auth_check.php";
requireAuth('admin');  // Enforce Administrator Role

$message = "";
$messageType = "";

// Configuration Constants for File Uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/posters/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2 MB Limit
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/**
 * Helper function to validate and upload poster files
 */
function processPosterUpload(array $file, array $allowedMimes, array $allowedExts, int $maxSize): array {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['status' => true, 'filename' => null]; // No file uploaded (optional)
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'error' => "File upload error code: " . $file['error']];
    }

    // 1. Validate File Size
    if ($file['size'] > $maxSize) {
        return ['status' => false, 'error' => "File size exceeds the 2 MB maximum allowed limit."];
    }

    // 2. Validate Extension
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedExts, true)) {
        return ['status' => false, 'error' => "Invalid file extension. Only JPG, PNG, and WEBP files are allowed."];
    }

    // 3. Validate MIME Type using Fileinfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes, true)) {
        return ['status' => false, 'error' => "Invalid file MIME type ($mimeType). File must be a valid image."];
    }

    // 4. Generate Cryptographically Safe Unique Filename
    $newFilename = "poster_" . bin2hex(random_bytes(8)) . "_" . time() . "." . $fileExt;
    $targetPath = UPLOAD_DIR . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['status' => true, 'filename' => $newFilename];
    }

    return ['status' => false, 'error' => "Failed to move uploaded file to target directory."];
}

// --------------------------------------------------------------------------
// 1. HANDLE POST ACTIONS (CREATE, UPDATE, DELETE)
// --------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // ACTION A: CREATE NEW EVENT
    if ($action === 'create') {
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $eventDate   = trim($_POST['event_date'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $maxCapacity = intval($_POST['max_capacity'] ?? 0);
        $status      = trim($_POST['status'] ?? 'UPCOMING');

        // Process File Upload
        $uploadResult = processPosterUpload($_FILES['poster'], $allowedMimeTypes, $allowedExtensions, MAX_FILE_SIZE);

        if (!$uploadResult['status']) {
            $message = $uploadResult['error'];
            $messageType = "danger";
        } else {
            $posterFilename = $uploadResult['filename'];

            $sql = "INSERT INTO events (title, category, event_date, location, max_capacity, poster_image, status) 
                    VALUES (:title, :category, :event_date, :location, :max_capacity, :poster, :status)";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([
                ':title'        => $title,
                ':category'     => $category,
                ':event_date'   => $eventDate,
                ':location'     => $location,
                ':max_capacity' => $maxCapacity,
                ':poster'       => $posterFilename,
                ':status'       => $status
            ]);

            if ($success) {
                $message = "Event added successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to add event to database.";
                $messageType = "danger";
            }
        }
    }

    // ACTION B: UPDATE EXISTING EVENT
    elseif ($action === 'update') {
        $eventId     = intval($_POST['event_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $eventDate   = trim($_POST['event_date'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $maxCapacity = intval($_POST['max_capacity'] ?? 0);
        $status      = trim($_POST['status'] ?? 'UPCOMING');
        $existingPoster = $_POST['existing_poster'] ?? null;

        $posterFilename = $existingPoster;

        // Check if a new file is uploaded
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = processPosterUpload($_FILES['poster'], $allowedMimeTypes, $allowedExtensions, MAX_FILE_SIZE);

            if (!$uploadResult['status']) {
                $message = $uploadResult['error'];
                $messageType = "danger";
            } else {
                $posterFilename = $uploadResult['filename'];

                // Remove old poster file if replacement succeeded
                if (!empty($existingPoster) && file_exists(UPLOAD_DIR . $existingPoster)) {
                    unlink(UPLOAD_DIR . $existingPoster);
                }
            }
        }

        if (empty($message)) {
            $sql = "UPDATE events 
                    SET title = :title, category = :category, event_date = :event_date, 
                        location = :location, max_capacity = :max_capacity, 
                        poster_image = :poster, status = :status 
                    WHERE event_id = :event_id";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([
                ':title'        => $title,
                ':category'     => $category,
                ':event_date'   => $eventDate,
                ':location'     => $location,
                ':max_capacity' => $maxCapacity,
                ':poster'       => $posterFilename,
                ':status'       => $status,
                ':event_id'     => $eventId
            ]);

            if ($success) {
                $message = "Event updated successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to update event details.";
                $messageType = "danger";
            }
        }
    }

    // ACTION C: DELETE EVENT
    elseif ($action === 'delete') {
        $eventId = intval($_POST['event_id'] ?? 0);

        if ($eventId > 0) {
            // Fetch poster to remove file from server
            $stmtSelect = $pdo->prepare("SELECT poster_image FROM events WHERE event_id = :id");
            $stmtSelect->execute([':id' => $eventId]);
            $event = $stmtSelect->fetch();

            if ($event && !empty($event['poster_image']) && file_exists(UPLOAD_DIR . $event['poster_image'])) {
                unlink(UPLOAD_DIR . $event['poster_image']);
            }

            $stmtDelete = $pdo->prepare("DELETE FROM events WHERE event_id = :id");
            if ($stmtDelete->execute([':id' => $eventId])) {
                $message = "Event and associated poster deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to delete event.";
                $messageType = "danger";
            }
        }
    }
}

// --------------------------------------------------------------------------
// 2. FETCH ALL EVENTS FOR LISTING (READ)
// --------------------------------------------------------------------------
$stmtFetch = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events = $stmtFetch->fetchAll();

// Fetch event details if editing
$editEvent = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmtEdit = $pdo->prepare("SELECT * FROM events WHERE event_id = :id");
    $stmtEdit->execute([':id' => $editId]);
    $editEvent = $stmtEdit->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Admin Event Management</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function confirmEventDelete(title) {
      return confirm("Are you sure you want to delete the event: '" + title + "'? All poster files will be permanently removed.");
    }
  </script>
</head>
<body>

<header class="site-header">
  <div class="container">
    <a href="#" class="brand">StudentHub Admin</a>
    <nav class="primary-nav">
      <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="events_admin.php" aria-current="page">Manage Events</a></li>
        <li><a href="logout.php" class="btn btn-outline btn-sm">Logout</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="container" style="margin-top:2rem;">
  <h1>Event Management Panel</h1>

  <!-- UI Feedback Alert Message Banner -->
  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'info' : 'danger'; ?>" 
         style="padding:1rem; border-radius:8px; margin-bottom:1.5rem; 
                background-color: <?php echo $messageType === 'success' ? '#d1fae5' : '#fee2e2'; ?>; 
                color: <?php echo $messageType === 'success' ? '#065f46' : '#991b1b'; ?>;">
      <strong><?php echo $messageType === 'success' ? 'Success:' : 'Error:'; ?></strong> <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <!-- Add / Edit Event Form -->
  <section class="panel" style="margin-bottom: 2.5rem;">
    <h2><?php echo $editEvent ? "Edit Event Record" : "Create New Event"; ?></h2>
    
    <form action="events_admin.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?php echo $editEvent ? "update" : "create"; ?>">
      <?php if ($editEvent): ?>
        <input type="hidden" name="event_id" value="<?php echo $editEvent['event_id']; ?>">
        <input type="hidden" name="existing_poster" value="<?php echo htmlspecialchars($editEvent['poster_image'] ?? ''); ?>">
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="form-group">
          <label for="title">Event Title</label>
          <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category" required>
            <option value="">-- Select Category --</option>
            <?php 
              $categories = ["Technical", "Competition", "Academic", "Cultural", "Sports"];
              foreach ($categories as $cat):
                $sel = ($editEvent && $editEvent['category'] === $cat) ? 'selected' : '';
            ?>
              <option value="<?php echo $cat; ?>" <?php echo $sel; ?>><?php echo $cat; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="event_date">Event Date & Time</label>
          <input type="datetime-local" id="event_date" name="event_date" 
                 value="<?php echo $editEvent ? date('Y-m-d\TH:i', strtotime($editEvent['event_date'])) : ''; ?>" required>
        </div>

        <div class="form-group">
          <label for="location">Location / Venue</label>
          <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($editEvent['location'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="max_capacity">Max Capacity</label>
          <input type="number" id="max_capacity" name="max_capacity" min="1" value="<?php echo htmlspecialchars($editEvent['max_capacity'] ?? '50'); ?>" required>
        </div>

        <div class="form-group">
          <label for="status">Event Status</label>
          <select id="status" name="status">
            <?php $statuses = ['UPCOMING', 'ONGOING', 'COMPLETED', 'CANCELLED']; ?>
            <?php foreach ($statuses as $st): ?>
              <option value="<?php echo $st; ?>" <?php echo ($editEvent && $editEvent['status'] === $st) ? 'selected' : ''; ?>>
                <?php echo $st; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="poster">Event Poster Image (JPG, PNG, WEBP — Max 2MB)</label>
          <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
          <?php if ($editEvent && !empty($editEvent['poster_image'])): ?>
            <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">
              Current Poster: <strong><?php echo htmlspecialchars($editEvent['poster_image']); ?></strong>
            </p>
          <?php endif; ?>
        </div>
      </div>

      <div style="margin-top:1.5rem; display:flex; gap:1rem;">
        <button type="submit" class="btn btn-primary"><?php echo $editEvent ? "Update Event" : "Create Event"; ?></button>
        <?php if ($editEvent): ?>
          <a href="events_admin.php" class="btn btn-outline">Cancel Editing</a>
        <?php endif; ?>
      </div>
    </form>
  </section>

  <!-- Events Directory Listing Table -->
  <section class="panel">
    <h2>Registered Events (<?php echo count($events); ?> Total)</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:1rem;">
      <thead>
        <tr style="background-color: #f8fafc;">
          <th>Poster</th>
          <th>Title</th>
          <th>Category</th>
          <th>Date & Time</th>
          <th>Location</th>
          <th>Capacity</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($events)): ?>
          <?php foreach ($events as $row): ?>
            <tr>
              <td style="text-align:center; width: 80px;">
                <?php if (!empty($row['poster_image']) && file_exists(UPLOAD_DIR . $row['poster_image'])): ?>
                  <img src="uploads/posters/<?php echo htmlspecialchars($row['poster_image']); ?>" 
                       alt="Poster" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                  <span style="font-size:0.75rem; color:#94a3b8;">No Poster</span>
                <?php endif; ?>
              </td>
              <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
              <td><span class="badge badge-category"><?php echo htmlspecialchars($row['category']); ?></span></td>
              <td><?php echo date('M d, Y H:i', strtotime($row['event_date'])); ?></td>
              <td><?php echo htmlspecialchars($row['location']); ?></td>
              <td><?php echo htmlspecialchars($row['max_capacity']); ?></td>
              <td>
                <span style="padding:0.25rem 0.5rem; border-radius:4px; font-size:0.75rem; font-weight:bold;
                             background-color: <?php echo $row['status'] === 'UPCOMING' ? '#dbeafe' : ($row['status'] === 'CANCELLED' ? '#fee2e2' : '#f3f4f6'); ?>;
                             color: <?php echo $row['status'] === 'UPCOMING' ? '#1e40af' : ($row['status'] === 'CANCELLED' ? '#991b1b' : '#374151'); ?>;">
                  <?php echo htmlspecialchars($row['status']); ?>
                </span>
              </td>
              <td>
                <div style="display:flex; gap:0.5rem;">
                  <a href="events_admin.php?edit_id=<?php echo $row['event_id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                  
                  <form action="events_admin.php" method="POST" onsubmit="return confirmEventDelete('<?php echo htmlspecialchars($row['title']); ?>');" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>">
                    <button type="submit" class="btn btn-sm" style="background:#ef4444; color:white; border:none; padding:0.4rem 0.8rem; border-radius:4px; cursor:pointer;">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center; padding:1.5rem;">No events found in the database.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
</main>

</body>
</html>
