<?php
  session_start();
  require "db_connection.php";

  function error_alert($url){
    echo "
    <script>
      alert('Incorrect username/password. Please try again.');
      document.location = '$url';
    </script>";
  }
  if(isset($_SESSION['user_id']))
   {
    header("Location: dashboard.php");
    exit();
   }
 
   if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $plain_password = $_POST['password'];
    $stmt = $con->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $stored_hash);
        $stmt->fetch();
        if (password_verify($plain_password, $stored_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $name;

            header("Location: dashboard.php");
            exit();
        } else {
            error_alert("login.php");
        }
    } else {
        error_alert("login.php");
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>CSCI 4060</title>
  <link rel="stylesheet" href="custom_style.css">
</head>
<body>
  <div id="content_div">
    <h1>Welcome to CSCI 4060</h1>
    <h2>Login with your credentials</h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <input type="text" name="email" placeholder="Enter your email" required><br><br>
      <input type="password" name="password" placeholder="Enter your password" required><br><br>
      <input type="submit" id="submit_btn" name="log_in_btn" value="Log In">
    </form>
    <h3>Not a user? <a href='registration.php'> Register Here!</a></h3>
  </div>
</body>
</html>
