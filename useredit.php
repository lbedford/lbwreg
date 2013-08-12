<?php
require("basefunc.inc.php");

$db = ConnectMysql();


CheckLoggedInOrRedirect();

$userid = $_SESSION["userid"];
$userstatus = $_SESSION["userstatus"];
$user = $_REQUEST["user"];

global $date, $xport, $acctype, $accorder;

if (!($userid == $user || $userstatus == 16)) {
  HtmlHead("useredit", "Illegal Action", "", "");
  printf("You're not allowed edit this user's information<br><ul>\n");
  HtmlTail();
  exit();
}

if (!isset($LBWID)) {
  $sql = "SELECT * FROM people2 WHERE id='$user'";
  $result = mysql_query($sql, $db);
  $row = mysql_fetch_array($result);

  HtmlHead("useredit", "User Information", $userstatus, $user);

  printf("\n<FORM METHOD=POST><INPUT TYPE=HIDDEN NAME=LBWID VALUE=$user>\n");
  printf("<table class='reginfo'>\n");
  printf("<tr><td>Login     </td><td><INPUT TYPE=TEXT name=logon VALUE = \"%s\" SIZE=35></td></tr>\n", htmlspecialchars($row["logon"]));
  printf("<tr><td>First Name</td><td> <INPUT TYPE=TEXT name=firstname VALUE = \"%s\" SIZE=35></td></tr>\n", htmlspecialchars($row["firstname"]));
  printf("<tr><td>Surname</td><td><INPUT TYPE=TEXT name=surname VALUE = \"%s\" SIZE=35></td></tr>\n", htmlspecialchars($row["surname"]));
  printf("<tr><td>Email</td><td><INPUT TYPE=TEXT name=email VALUE= \"%s\" SIZE=35></td></tr>\n", htmlspecialchars($row["email"]));
  printf("<tr><td>City</td><td><INPUT TYPE=TEXT name=city VALUE=\"%s\" SIZE=35></td></tr>\n", htmlspecialchars($row["city"]));
  printf("<tr><td>Country</td><td><SELECT name=country>");
  $sql = "SELECT * FROM country ORDER BY name";
  $rx = mysql_query($sql, $db);
  while ($pays = mysql_fetch_array($rx)) {
    $sel = ($pays["id"] == $row["country"]) ? "SELECTED" :
        "";
    printf("<OPTION VALUE=%d %s>%s\n", $pays["id"], $sel, $pays["name"]);
  }
  printf("</select></td></tr>\n");

  printf("<tr><td>number of children</td><td><INPUT TYPE=TEXT name = children VALUE=\"%s\" SIZE=4></td></tr>\n", $row["children"]);
  printf("<tr><td>Arrival</td><td><SELECT name = arrival>");
  for ($i = 0; $i < count($date) - 1; $i++) {
    $sel = ($row["arrival"] == $i && !is_null($row["arrival"])) ?
        "SELECTED" : "";
    printf("<OPTION VALUE=%d %s>%s\n", $i, $sel, $date[$i]);
  }
  printf("<OPTION VALUE=NULL %s>Unknown\n", is_null($row["arrival"]) ?
      "SELECTED" : "");
  printf("</select></td></tr>\n");

  printf("<tr><td>Departure</td><td><SELECT name = departure>");
  for ($i = 0; $i < count($date) - 1; $i++) {
    $sel = ($row["departure"] == $i && !is_null($row["departure"])) ?
        "SELECTED" : "";
    printf("<OPTION VALUE=%d %s>%s\n", $i, $sel, $date[$i]);
  }
  printf("<OPTION VALUE=NULL %s>Unknown\n", is_null($row["departure"]) ?
      "SELECTED" : "");
  printf("</select></td></tr>\n");

  printf("<tr><td>Travelling by</td><td><SELECT name = travelby>");
  for ($i = 0; $i < count($xport); $i++) {
    $sel = ($i == $row["travelby"]) ? "SELECTED" :
        "";
    printf("<OPTION VALUE = %s %s>%s\n", $i, $sel, $xport[$i]);
  }
  printf("</select></td></tr>\n");
  printf("<tr><td>Kind of Accommodation</td><td><SELECT name = kindofaccomodation>\n");
  for ($i = 0; $i < count($acctype); $i++) {
    $sel = ($accorder[$i] == $row["kindofaccomodation"]) ? "SELECTED" : "";
    printf("<OPTION VALUE = %s %s>%s\n", $i, $sel, $acctype[$accorder[$i]]);
  }
  printf("</select></td></tr>\n");

  printf("<tr><td>Name of Accomodation<br>if known</td><td><INPUT TYPE=TEXT name = nameofaccomodation VALUE=\"%s\" SIZE=35></td></tr>\n", htmlspecialchars($row["nameofaccomodation"]));

  printf("<tr><td>Attending?</td><td><INPUT TYPE=CHECKBOX name=attending VALUE=\"1\" %s></td></tr>\n", $row["attending"] == 1 ? "checked=checked" : "");

  echo "<tr>";
  echo "<TD>";
  echo "<INPUT TYPE=SUBMIT NAME=submit VALUE=SAVE>";
  echo "</td>";
  echo "<TD>";
  echo "<INPUT TYPE=SUBMIT NAME=submit VALUE=CANCEL>";
  echo "</td>";
  echo "</tr>\n";
  echo "</table>\n";
  echo "</form>\n";
  HtmlTail();
  exit();
} else {
  $LBWID = mysql_real_escape_string(trim($LBWID));
  $submit = GetEntryFromRequest('submit', 'CANCEL');
  $logon = GetEntryFromRequest('logon', '');
  $firstname = GetEntryFromRequest('firstname', 'Reto');
  $surname = GetEntryFromRequest('surname', 'Schmidt');
  $email = GetEntryFromRequest('email', 'nobody@local.xxx');
  $city = GetEntryFromRequest('city', 'Accra');
  $country = GetEntryFromRequest('country', 1);
  $attending = intval(GetEntryFromRequest('attending', 0));
  $children = intval(GetEntryFromRequest('children', 0));
  $arrival = GetEntryFromRequest('arrival', 'null');
  $departure = GetEntryFromRequest('departure', 'null');
  $travelby = GetEntryFromRequest('travelby', 'null');
  $kindofaccomodation = GetEntryFromRequest('kindofaccomodation', 'null');
  $nameofaccomodation = GetEntryFromRequest('nameofaccomodation', 'null');
  if ($submit == 'CANCEL') {
    header("Location: welcome.php");
    exit();
  }
  if (!($LBWID == $userid || $userstatus == 16)) {
    HtmlHead("useredit", "User Edit", "", "");
    printf("There seems to be a SNAFU!<br>Session details are missing at bottom<br>");
    printf("<A HREF=login.php>Continue</a>\n");
    HtmlTail();
  }
  $err = 0;
  $error = Array();
  if (($departure < $arrival) && ($departure != "NULL") && ($arrival != "NULL")) {
    $err++;
    $error[$err] = "You can not leave before you arrive";
  }
  //more error checking
  $result = mysql_query("SELECT logon FROM people2 WHERE id='$LBWID'", $db);
  $row = mysql_fetch_array($result);
  if ($row["logon"] != $logon) {
    if (strlen($logon) < 4) {
      echo "login must be at least 4 letters<br>\n";
      $err++;
    }
    $result = mysql_query("SELECT id,logon FROM people2 WHERE (logon LIKE '$logon') AND (id != '$LBWID')", $db);
    if (!$result) {
      echo mysql_error($db) . "<br>\n";
      exit();
    }
    if (mysql_num_rows($result) > 0) {
      $err++;
      echo "Login \"" . htmlspecialchars($logon) . "\" is already in use<br>\n";
    }
  }
  if ($err == 0) {
    $sql = "UPDATE people2 SET logon = '$logon',email = '$email', city='$city', country=$country, attending=$attending, children='$children', arrival=$arrival, departure=$departure, travelby='$travelby', kindofaccomodation='" . $accorder[$kindofaccomodation] . "', nameofaccomodation='$nameofaccomodation', firstname='$firstname', surname='$surname'  WHERE id='$LBWID'";
    $result = mysql_query($sql, $db);
    if (!$result) {
      HtmlHead("useredit", "Database Error", $userstatus, $userid);
      printf("%s", mysql_error());
      HtmlTail();
      exit();
    }
    header("Location: userview.php?user=$LBWID");
    exit();
  }
  HtmlHead("useredit", "Inconsistent data", "", "");
  printf("Your Form contains inconsistent data<br><ul>\n");
  for ($i = 1; $i <= $err; $i++)
    printf("<li>%s<li>\n", $error[$i]);
  printf("</ul><br><A HREF=useredit.php>CONTINUE</a><br>\n");
  HtmlTail();
}