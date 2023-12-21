<?php
// Replace these with your actual database credentials
include("_functions.php");
$conn=dbconn();
if (!$conn) {
    die("koneksi error");
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect username and password from the form
    $enteredUsername = $_POST['username'];
    $enteredPassword = $_POST['password'];

    // Perform SQL insertion (This is just an example, please hash passwords properly in a real scenario)
    if($enteredUsername != "" && $enteredPassword != "")
    $sql = "INSERT INTO USR (Username, Password)
    VALUES (\"$enteredUsername\", SHA2(CONCAT(\"$enteredUsername\", \"$enteredPassword\"), 256));";
    mysqli_query($conn, $sql);
    
    $sql = "INSERT INTO DRCT_MSG (Sender, Receiver, Message) VALUES ((SELECT ID FROM USR WHERE Username = \"$enteredUsername\"), (SELECT ID FROM USR WHERE Username = \"$enteredUsername\"), \"Welcome\")";
    mysqli_query($conn, $sql);

    $_POST['username'] = "";
    $_POST['password'] = "";
    // if ($conn->query($sql) === TRUE) {
    //     echo "New record created successfully";
    // } else {
    //     echo "Error: " . $sql . "<br>" . $conn->error;
    // }
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Additional CSS for the registration form */
    .register-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .register-box {
      width: 300px;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
  </style>
  <link rel="icon" href="img/favicon.ico" type="img/x-icon">
</head>
<body>

  <div class="register-container bg-dark">
    <div class="register-box">
      <h2 class="text-center mb-4">Register</h2>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
        </div>
        <a href="login.php"><button type="submit" name="register_button" class="btn btn-success w-100">Register</button></a>
      </form>
</form>
      
      <div class="text-center mt-3">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies (not required for the layout) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
