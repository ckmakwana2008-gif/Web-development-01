document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registration-form");
  const REGEX = {
    fullname: /^[a-zA-Z\s]{3,50}$/, 
    email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
    mobile: /^[6-9]\d{9}$/ 
  };

  const showError = (inputId, errorId, message) => {
    const input = document.getElementById(inputId) || document.querySelector(`[name="${inputId}"]`);
    const errorSpan = document.getElementById(errorId);
    
    if (input) {
      input.setAttribute("aria-invalid", "true");
      input.classList.add("input-error");
    }
    if (errorSpan) {
      errorSpan.textContent = message;
      errorSpan.style.display = "block";
    }
  };

  const clearError = (inputId, errorId) => {
    const input = document.getElementById(inputId) || document.querySelector(`[name="${inputId}"]`);
    const errorSpan = document.getElementById(errorId);

    if (input) {
      input.removeAttribute("aria-invalid");
      input.classList.remove("input-error");
    }
    if (errorSpan) {
      errorSpan.textContent = "";
      errorSpan.style.display = "none";
    }
  };

  const checkPasswordStrength = (password) => {
    const bar = document.getElementById("strength-bar");
    const text = document.getElementById("password-strength");
    let score = 0;

    if (!password) {
      if (bar) bar.style.width = "0%";
      if (text) text.textContent = "";
      return score;
    }

    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    let strengthLabel = "";
    let color = "";
    let width = "0%";

    switch (score) {
      case 1:
      case 2:
        strengthLabel = "Weak";
        color = "#ef4444";
        width = "33%";
        break;
      case 3:
      case 4:
        strengthLabel = "Moderate";
        color = "#f59e0b";
        width = "66%";
        break;
      case 5:
        strengthLabel = "Strong";
        color = "#10b981";
        width = "100%";
        break;
    }

    if (bar) {
      bar.style.width = width;
      bar.style.backgroundColor = color;
    }
    if (text) {
      text.textContent = `Password Strength: ${strengthLabel}`;
      text.style.color = color;
    }

    return score;
  };

  const passwordInput = document.getElementById("password");
  passwordInput?.addEventListener("input", (e) => {
    checkPasswordStrength(e.target.value);
  });
  const validateField = (id) => {
    let isValid = true;

    switch (id) {
      case "fullname": {
        const val = document.getElementById("fullname").value.trim();
        if (!val) {
          showError("fullname", "fullname-error", "Full Name is required.");
          isValid = false;
        } else if (!REGEX.fullname.test(val)) {
          showError("fullname", "fullname-error", "Name must contain only letters (min 3 chars).");
          isValid = false;
        } else {
          clearError("fullname", "fullname-error");
        }
        break;
      }
      case "email": {
        const val = document.getElementById("email").value.trim();
        if (!val) {
          showError("email", "email-error", "Email Address is required.");
          isValid = false;
        } else if (!REGEX.email.test(val)) {
          showError("email", "email-error", "Please enter a valid email address (e.g., user@domain.com).");
          isValid = false;
        } else {
          clearError("email", "email-error");
        }
        break;
      }
      case "mobile": {
        const val = document.getElementById("mobile").value.trim();
        if (!val) {
          showError("mobile", "mobile-error", "Mobile number is required.");
          isValid = false;
        } else if (!REGEX.mobile.test(val)) {
          showError("mobile", "mobile-error", "Enter a valid 10-digit mobile number starting with 6-9.");
          isValid = false;
        } else {
          clearError("mobile", "mobile-error");
        }
        break;
      }
      case "password": {
        const val = document.getElementById("password").value;
        if (!val) {
          showError("password", "password-error", "Password is required.");
          isValid = false;
        } else if (val.length < 8) {
          showError("password", "password-error", "Password must be at least 8 characters long.");
          isValid = false;
        } else {
          clearError("password", "password-error");
        }
        break;
      }
      case "confirm-password": {
        const passVal = document.getElementById("password").value;
        const confirmVal = document.getElementById("confirm-password").value;
        if (!confirmVal) {
          showError("confirm-password", "confirm-password-error", "Please confirm your password.");
          isValid = false;
        } else if (passVal !== confirmVal) {
          showError("confirm-password", "confirm-password-error", "Passwords do not match.");
          isValid = false;
        } else {
          clearError("confirm-password", "confirm-password-error");
        }
        break;
      }
      case "course": {
        const val = document.getElementById("course").value;
        if (!val) {
          showError("course", "course-error", "Please select a course.");
          isValid = false;
        } else {
          clearError("course", "course-error");
        }
        break;
      }
      case "year": {
        const val = document.getElementById("year").value;
        if (!val) {
          showError("year", "year-error", "Please select your academic year.");
          isValid = false;
        } else {
          clearError("year", "year-error");
        }
        break;
      }
      case "gender": {
        const selected = document.querySelector('input[name="gender"]:checked');
        if (!selected) {
          showError("gender", "gender-error", "Please select your gender.");
          isValid = false;
        } else {
          clearError("gender", "gender-error");
        }
        break;
      }
      case "terms": {
        const checked = document.getElementById("terms").checked;
        if (!checked) {
          showError("terms", "terms-error", "You must accept the terms and conditions.");
          isValid = false;
        } else {
          clearError("terms", "terms-error");
        }
        break;
      }
    }
    return isValid;
  };

  const inputIds = ["fullname", "email", "mobile", "password", "confirm-password", "course", "year", "terms"];
  inputIds.forEach((id) => {
    const el = document.getElementById(id);
    el?.addEventListener("blur", () => validateField(id));
  });

  form?.addEventListener("submit", (e) => {
    e.preventDefault();

    let isFormValid = true;
    const fieldsToValidate = ["fullname", "email", "mobile", "password", "confirm-password", "course", "year", "gender", "terms"];

    fieldsToValidate.forEach((field) => {
      const valid = validateField(field);
      if (!valid) isFormValid = false;
    });

    if (isFormValid) {
      alert("Registration Successful! Account created for StudentHub.");
      form.reset();
      checkPasswordStrength("");
    } else {
      const firstInvalid = form.querySelector('[aria-invalid="true"]');
      firstInvalid?.focus();
    }
  });
});
