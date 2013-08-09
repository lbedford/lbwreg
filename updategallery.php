<?php
include("basefunc.inc.php");

session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
}
$db = connectmysql();

$result = mysql_query("SELECT id FROM people2", $db);
while ($row = mysql_fetch_array($result)) {
  $filename = "mid_" . $row["id"] . ".jpg";

  print "Checking for file $filename<br>\n";
  if (file_exists("pictures/" . $filename)) { //is there a mug shot?
    $sql = "SELECT lbwid from whois where lbwid='" . $row["id"] . "'"; // is this file already listed in whois?
    if (!mysql_num_rows(mysql_query($sql, $db))) {
      $sql = "INSERT into whois (lbwid, galpix) VALUES ('" . $row["id"] . "', '" . $filename . "')"; //if ! add it
      $return = mysql_query($sql, $db);
    } else {
      $sql = "UPDATE whois set galpix = '$filename' where lbwid = " . $row["id"];
      $return = mysql_query($sql, $db);
    }
    if ($return == FALSE) {
      print "<<$sql>> failed with: " . mysql_error() . "<br>\n";
    }
    $sql = "UPDATE people2 set whois=1 WHERE id='" . $row["id"] . "'";
    $return = mysql_query($sql, $db);
    if ($return == FALSE) {
      print "<<$sql>> failed with: " . mysql_error() . "<br>\n";
    }
  }
}
header("Location: gallery.php");