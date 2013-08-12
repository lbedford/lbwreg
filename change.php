<?php
require("basefunc.inc.php");

session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

$db = ConnectMysql();
HtmlHead("change", "", $_SESSION["userstatus"], $_SESSION["userid"]);

/* first get firstname/surname from the environment POST/GET */
$old_password = $_REQUEST["old_password"];
$password = $_REQUEST["password"];
$verify = $_REQUEST["verify"];

$query = "SELECT firstname,surname,password,status FROM people2 WHERE id = '" . $_SESSION["userid"] . "'";

$result = mysql_query($query, $db);

if (!$result) {
  printf("%s<br>\n", mysql_error());
}

$row = mysql_fetch_array($result);

if (crypt($old_password, $row["password"]) == $row["password"]) {
  if ($row["status"] < 2) {
    HtmlHead("verify", "Verification failed", $_SESSION["userstatus"], $_SESSION["userid"]);

    echo "<h3>Sorry.  You don't seem to have access rights to this site yet.<br>\n";
    echo "Please contact an administrator to have your access approved.</h3><br><hr>";
    HtmlTail();
    exit();
  }

  if ($password != $verify) {
    echo " <h1> Password and verification don't match </h1><br>";
    echo " Please go back and try again<br>";
    HtmlTail();
    exit();
  }

  // update password with crypted one
  $crypted_password = crypt($password);
  $update_query = "UPDATE people2 set password = '" . $crypted_password . "' where id = '" . $_SESSION["userid"] . "'";
  if (!mysql_query($update_query, $db)) {
    echo " <h1> PROBLEM UPDATING PASSWORD</h1>";
    HtmlTail();
    exit();
  }

  echo "<h4>";
  echo "Your login password  has been updated.\n";
  echo "<li><A HREF='" . $regpath . "/welcome.php'> Go to the welcome page</a></li></ul>";
  echo "";
} else {
  echo "<h2>Login rejected<br>";
  echo "Your old password was incorrect<br>";
  echo "</h2>";
  HtmlTail();
}

HtmlTail();