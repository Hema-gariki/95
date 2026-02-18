<?php
  if (isset($_GET['action'])) {
    $action = $_GET['action'];
    // Handle cookie update
    $cookie_name = "user";
     if ($action == "update") {
        $user = $_GET['value'];
        setcookie($cookie_name, $user , time() + 300, "/");
    }

    // Handle cookie removal
    if ($action == "remove") {
        setcookie($cookie_name, "", time() - 300, "/");
    }

    // Reload Page
    header("Location: cookie.php");
    exit();
    }
?>

<html>
  <head>
    <link rel="stylesheet" href="custom_style.css">
  </head>
  <body>
  <div id="cookie_div">
  <?php
    $cookie_name = "user";
    if(!isset($_COOKIE[$cookie_name])) {
      echo "Cookie is not set for this site!";
    } else {
      echo "Cookie '" . $cookie_name . "' is set!<br>";
      echo "Value is: " . $_COOKIE[$cookie_name];
    }
    include "cookie_input.html";
  ?>
  </div>
  </body>
</html>