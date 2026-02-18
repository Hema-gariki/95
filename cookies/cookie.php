<?php
$cookie_name = "user";

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Update cookie
    if ($action == "update" && isset($_GET['value']) && $_GET['value'] != "") {
        $user = $_GET['value'];
        setcookie($cookie_name, $user, time() + 300, "/");
    }

    // Remove cookie
    if ($action == "remove") {
        setcookie($cookie_name, "", time() - 300, "/");
    }

    // Reload page
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
if (!isset($_COOKIE[$cookie_name])) {
    echo "Cookie is not set for this site!";
} else {
    echo "Cookie '" . $cookie_name . "' is set!<br>";
    echo "Value is: " . $_COOKIE[$cookie_name];
}

include "cookie_index.html";
?>
</div>
</body>
</html>
