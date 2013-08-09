<?php
require("basefunc.inc.php");

function namecheck($db, $firstname, $surname)
{
  $query = "SELECT id,firstname,surname FROM people2 WHERE (firstname LIKE '$firstname') AND (surname LIKE '$surname')";
  $result = mysql_query($query, $db);
  If (!$result)
    printf("<br>%S<br>", mysql_error($db));
  if (!mysql_num_rows($result)) {
    $query = "SELECT id,firstname,surname FROM people2 WHERE (firstname LIKE '$surname') AND (surname LIKE '$firstname')";
    $result = mysql_query($query, $db);
    If (!$result)
      printf("<br>%S<br>", mysql_error($db));
  }
  if (!mysql_num_rows($result))
    return 0;
  $row = mysql_fetch_array($result);
  return $row["id"];
}

$db = ConnectMysql();
HtmlHead("reset", "", 1, 0);

/* first get firstname/surname from the environment POST/GET */
$firstname = $_REQUEST["firstname"];
$surname = $_REQUEST["surname"];

/* then protect them against malicious MYSQL commands */
$firstname = mysql_real_escape_string($firstname);
$surname = mysql_real_escape_string($surname);

$firstname = str_replace("%", "|", $firstname);
$surname = str_replace("%", "|", $surname);

echo "Checking \"$firstname $surname\"...<br>";
$uid = namecheck($db, $firstname, $surname);
if (!$uid) {
  echo " <h1> NOT REGISTERED</h1>";
  HtmlTail();
  exit();
}

// generate new password
$retval = 0;

$old_password = "";
for ($i = 0; $i < 8; $i++) {
  $old_password .= chr(mt_rand(42, 90));
}

$cmdline = "/usr/bin/apg -m 8 -x 12 -d -n 1 -c \"" . escapeshellarg($old_password) . "\"";
$new_password = exec($cmdline, $cmd_output, $retval);
if ($retval != 0) {
  echo " <h1> PROBLEM GENERATING PASSWORD</h1>";
  echo " This command --" . $cmdline . "-- failed<br>";
  print_r($cmd_output);
  echo " Return Value --" . $retval . "--<br>";
  HtmlTail();
  exit();
}

$crypted_password = crypt($new_password);

$update_query = "UPDATE people2 set password = '" . $crypted_password . "' where id = '" . $uid . "'";
if (!mysql_query($update_query, $db)) {
  echo " <h1> PROBLEM UPDATING PASSWORD</h1>";
  HtmlTail();
  exit();
}

$row = mysql_fetch_array(mysql_query("SELECT logon,email FROM people2 WHERE id='$uid'", $db));
$password = $new_password;
$email = $row["email"];
$logon = $row["logon"];
$message = "Hello $firstname $surname,\r\nHere are the requested login details for the LBW $year website.\r\n";
$message .= "Login: $logon\r\nPassword: $password\r\n";
$message .= "\r\nPlease send any comments to $teammail\r\n";
if (mail($email, "Your login details", $message, "From: $teammail")) {
  echo "<h4>";
  echo "Your login information has been sent to the email address that you gave when you registered.\n";
  echo "You should receive it shortly.<br></h4><hr>\n";
  echo "";
  echo "<ul>\n<li><A HREF='login.php'>Return to login page while waiting</a></li>";
  echo "<li><A HREF='$eventhost'> Go to the event main page</a></li></ul>";
  echo "";
} else {
  echo "mail problem<br>";
}

HtmlTail();