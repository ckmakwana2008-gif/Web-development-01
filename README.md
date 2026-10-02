[Uploading README.md…]()<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in — StudentHub</title>
<meta name="description" content="Log in to your StudentHub account.">
<link rel="stylesheet" href="style.css">
</head>
<body>

<a class="skip-link" href="#main-content">Skip to main content</aa>

<header class="site-header">
  <div class="container">
    <a class="brand" href="index.html">
      <span class="brand-mark">StudentHub</span>
      <span class="brand-tag">Student Portal</span>
    </a>
    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="events.html">Events</a></li>
        <li><a href="faq.html">FAQ</a></li>
        <li><a href="contact.html">Contact</a></li>
        <li><a href="login.html" aria-current="page">Log in</a></li>
        <li><a href="register.html">Register</a></li>
      </ul>
    </nav>
  </div>
</header>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="container">
    <ol>
      <li><a href="index.html">Home</a></li>
      <li aria-current="page">Log in</li>
    </ol>
  </div>
</nav>

<main id="main-content">
  <div class="container">
    <h1>Log in to StudentHub</h1>
    <p>Enter your registered email and password to reach your dashboard.</p>

    <form class="form-grid" action="dashboard.html" method="get" novalidate>
      <div>
        <label for="login-email">Email <span class="required-marker" aria-hidden="true">*</span></label>
        <input type="email" id="login-email" name="email" autocomplete="email" required aria-required="true">
      </div>

      <div>
        <label for="login-password">Password <span class="required-marker" aria-hidden="true">*</span></label>
        <input type="password" id="login-password" name="password" autocomplete="current-password" required aria-required="true">
      </div>

      <div class="checkbox-group">
        <label for="remember-me">
          <input type="checkbox" id="remember-me" name="remember-me">
          Remember me on this device
        </label>
      </div>

      <button type="submit" class="btn btn-dark">Log in</button>
    </form>

    <p><a href="#">Forgot your password?</a></p>
    <p>New to StudentHub? <a href="register.html">Create an account</a>.</p>
  </div>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <nav class="footer-nav" aria-label="Footer">
        <ul>
          <li><a href="about.html">About</a></li>
          <li><a href="faq.html">FAQ</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="feedback.html">Feedback</a></li>
        </ul>
      </nav>
      <p>Questions? <a href="contact.html">Get in touch</a>.</p>
    </div>
    <p class="footer-fine">&copy; 2026 StudentHub. Built as a semester-long academic project.</p>
  </div>
</footer>
<script src="main.js"></script>
</body>
</html>

