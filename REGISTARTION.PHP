<?php
require_once "db_mysqli.php";

$errors = [];
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rawUsername = $_POST['username'] ?? '';
    $rawEmail    = $_POST['email'] ?? '';
    $rawFullName = $_POST['full_name'] ?? '';
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $username = filter_var(trim($rawUsername), FILTER_SANITIZE_SPECIAL_CHARS);
    $email    = filter_var(trim($rawEmail), FILTER_SANITIZE_EMAIL);
    $fullName = filter_var(trim($rawFullName), FILTER_SANITIZE_SPECIAL_CHARS);

    // Username Check
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errors['username'] = "Username must be 3-20 alphanumeric characters or underscores.";
    }

    // Email Check
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
    }

    // Full Name Check
    if (empty($fullName)) {
        $errors['full_name'] = "Full Name is required.";
    }

    // Password Validation
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    if ($password !== $confirmPass) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $checkQuery = "SELECT user_id, username, email FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmtCheck = $conn->prepare($checkQuery);
        $stmtCheck->bind_param("ss", $username, $email);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($existingUser = $result->fetch_assoc()) {
            if ($existingUser['username'] === $username) {
                $errors['username'] = "Username is already registered.";
            }
            if ($existingUser['email'] === $email) {
                $errors['email'] = "Email address is already registered.";
            }
        }
        $stmtCheck->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $insertQuery = "INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->bind_param("ssss", $username, $email, $hashedPassword, $fullName);

        if ($stmtInsert->execute()) {
            $successMsg = "Registration successful! You can now log in.";
            $_POST = []; // Clear inputs
        } else {
            $errors['general'] = "Registration failed due to a system error. Please try again.";
        }
        $stmtInsert->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StudentHub - Secure Registration</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="container form-container">
  <h1>Create StudentHub Account</h1>
  
  <?php if (!empty($successMsg)): ?>
    <div class="alert alert-info" style="background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
      <strong>Success!</strong> <?php echo $successMsg; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger" style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
      <?php echo $errors['general']; ?>
    </div>
  <?php endif; ?>

  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
    <!-- Full Name -->
    <div class="form-group">
      <label for="full_name">Full Name <span class="required">*</span></label>
      <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
      <?php if (!empty($errors['full_name'])): ?>
        <span class="error-message" style="display:block;"><?php echo $errors['full_name']; ?></span>
      <?php endif; ?>
    </div>

    <!-- Username -->
    <div class="form-group">
      <label for="username">Username <span class="required">*</span></label>
      <input type="text" id="username" name="username" pattern="^[a-zA-Z0-9_]{3,20}$" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
      <?php if (!empty($errors['username'])): ?>
        <span class="error-message" style="display:block;"><?php echo $errors['username']; ?></span>
      <?php endif; ?>
    </div>

    <!-- Email -->
    <div class="form-group">
      <label for="email">Email Address <span class="required">*</span></label>
      <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      <?php if (!empty($errors['email'])): ?>
        <span class="error-message" style="display:block;"><?php echo $errors['email']; ?></span>
      <?php endif; ?>
    </div>

    <!-- Password -->
    <div class="form-group">
      <label for="password">Password <span class="required">*</span></label>
      <input type="password" id="password" name="password" minlength="8" required>
      <?php if (!empty($errors['password'])): ?>
        <span class="error-message" style="display:block;"><?php echo $errors['password']; ?></span>
      <?php endif; ?>
    </div>

    <!-- Confirm Password -->
    <div class="form-group">
      <label for="confirm_password">Confirm Password <span class="required">*</span></label>
      <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
      <?php if (!empty($errors['confirm_password'])): ?>
        <span class="error-message" style="display:block;"><?php echo $errors['confirm_password']; ?></span>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Register Account</button>
  </form>
</main>

</body>
</html>
