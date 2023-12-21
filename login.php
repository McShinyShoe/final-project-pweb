<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Additional CSS for the square and login form */
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      width: 300px;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
  </style>
      <script>
        function hashAndSubmit() {
            // Get the value from the input field
            var user = document.getElementById("username").value;
            var pass = document.getElementById("password").value;
            var salted = user.concat(pass);
            
            // Apply SHA256 hashing
            var hashedValue = sha256(salted);
            
            // Set the hashed value to a hidden input field in the form
            document.getElementById("token").value = hashedValue;
        }
    </script>
  <link rel="icon" href="img/favicon.ico" type="img/x-icon">
</head>
<body>
  <div class="login-container bg-dark">
    <div class="login-box">
      <h2 class="text-center mb-4">Login</h2>
      <form action="index.php">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="usr" placeholder="Enter username">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" placeholder="Enter password">
        </div>
        <input type="hidden" id="token" name="token">
        <button type="submit" class="btn btn-success w-100" onclick="hashAndSubmit()">Login</button>
      </form>
      <div class="text-center mt-3">
        <p>Don't have an account? <a href="register.php">Register</a></p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies (not required for the layout) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js"></script>
</body>
</html>
