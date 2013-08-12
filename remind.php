<?php
require("basefunc.inc.php");

function mailcheck($db, $newmail)
{
  // returns 0 on invalid -1 if on list but not registered or userid if in use
  if (strlen($newmail) < 8)
    return 0;
  $mailbits = explode("@", $newmail, 7);
  if (($q = count($mailbits)) != 2) {
    echo "mailbits != 2! it is $q<br>";
    return 0;
  }
  if (count(explode(".", $mailbits[1], 2)) != 2) {
    echo "explode count != 2<br>";
    return 0;
  }
  $result = mysql_query("SELECT id,logon FROM people2 WHERE (logon LIKE '$newmail') OR (email LIKE '$newmail')", $db);
  if (mysql_numrows($result) > 0) {
    $row = mysql_fetch_array($result);
    return $row["id"];
  }
  if (mysql_numrows(mysql_query("SELECT * FROM maillist WHERE '$newmail' LIKE listadrs", $db)) > 0)
    return -1;
  return 0;
}


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
HtmlHead("remind", "", $_SESSION["userstatus"], $_SESSION["userid"]);

/* first get firstname/surname/newmail from the environment POST/GET */
$firstname = $_REQUEST["firstname"];
$surname = $_REQUEST["surname"];
$newmail = $_REQUEST["newmail"];

/* then protect them against malicious MYSQL commands */
$firstname = mysql_real_escape_string($firstname);
$surname = mysql_real_escape_string($surname);
$newmail = mysql_real_escape_string($newmail);

$firstname = str_replace("%", "|", $firstname);
$surname = str_replace("%", "|", $surname);

echo "Checking \"$firstname $surname\" with newmail \"$newmail\"...<br>";
$uid = namecheck($db, $firstname, $surname);
if (!$uid) {

  echo " <h1> NOT REGISTERED</h1>";
  HtmlTail();
  exit();
}

$eid = mailcheck($db, $newmail);
switch ($eid) {
  case -1:
    $status = 2; //newmail is on the list and not in use;
    break;
  case 0:
    $status = 1; //no valid newmail but user checks out;
    break;
  default:
    $status = ($eid == $uid) ? 3 :
        4; //3 its either his old or new email as 1 , 4 its a spoof: report it;
}

switch ($status) {
  case 1: // name match no newmail or invalid newmail
    $row = mysql_fetch_array(mysql_query("SELECT logon,email,password FROM people2 WHERE id='$uid'", $db));
    $password = $row["password"];
    $email = $row["email"];
    $logon = $row["logon"];
    $message = "Hello $firstname $surname,\r\nHere are the requested login details for the LBW $year website.\r\n";
    $message .= "Login: $logon\r\nPassword: $password\r\n";
    $message .= "or paste the quick-link below to your bookmarks\r\n";
    $message .= $regpath . "/verify?logon=" . urlencode($logon) . "&password=" . urlencode($password) . "\r\n";
    $message .= "\r\nPlease send any comments to $teammail\r\n";
    if (mail($email, "Your login details", $message, "From: $teammail")) {
      echo "<h4>";
      echo "Your login information has been sent to the email address that you gave when you registered.\n";
      echo "You should receive it shortly.<br></h4><hr>\n";
      echo "";
      echo "<ul>\n<li><A HREF='login.php'>Return to login page while waiting</a></li>";
      echo "<li><A HREF='" . $eventhost . "'> Go to the event main page</a></li></ul>";
      echo "";
    } else {
      echo "mail problem<br>";
    }
    break;
  case 2:
  case 3:
    $row = mysql_fetch_array(mysql_query("SELECT logon,email,password FROM people2 WHERE id='$uid'", $db));
    $password = $row["password"];
    $email = $newmail;
    $logon = $row["logon"];
    $message = "Hello $firstname $surname\r\nHere are the requested login details for the LBW $year Website.\r\n";
    $message .= "Login: $logon\r\nPassword: $password\r\n";
    $message .= "or paste the quick-link below to your bookmarks\r\n";

    $message .= "$regpath/verify?logon=" . urlencode($logon) . "&password=" . urlencode($password) . "\r\n";
    $message .= "\r\nPlease send any comments to $teammail\r\n";
    mail($email, "Your login details", $message, "From: $teammail");

    echo "<h2>";
    echo "Your login information has been sent to $newmail <br>\n";
    echo "You should receive it shortly<br></h2><hr>\n";
    echo "";
    echo "<ul>\n<li><A HREF=login.php>Return to login page while waiting</a></li>";
    echo "<li><A HREF='" . $eventhost . "'> Go to the Lbw Home Page</a></li></ul>";
    echo "";
    break;
  case 4:
    $row = mysql_fetch_array(mysql_query("SELECT firstname,surname FROM people2 WHERE id='$eid'", $db));

    $message = "HACK ALERT\r\n at " . date("H:i:s  D j Y", time()) . " a person claiming to be \"$firstname $surname\" ($uid)";
    $message .= "requested login details be sent to \"$newmail\" which belongs to " . $row["firstname"] . " " . $row["surname"] . "\r\n";
    $message .= "REMOTE_ADDRESS:= " . $_SERVER['REMOTE_ADDR'] . "\r\n";
    $message .= "REMOTE_HOST:= " . $_SERVER['REMOTE_HOST'] . "\r\n";
    $message .= "USER AGENT:= " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
    mail("$teammail", "HACK ALERT", $message, "2007@lbwand.org");

    $message .= "USER AGENT:= " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";

    echo "<h3>The email address that you gave is in use by another user; can't send you his login details.</h3>\n";

    break;
}


HtmlTail();