<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();

$userstatus = $_SESSION["userstatus"];
$userid = $_SESSION["userid"];
$user = mysql_real_escape_string(trim($_REQUEST["user"]));
global $date, $xport, $acctype;
$db = ConnectMysql();

$sql = "SELECT firstname, surname, city, country.name as name, " .
    "arrival, departure, attending, children, travelby, status, " .
    "kindofaccomodation, nameofaccomodation, logon, email FROM " .
    "people2,country WHERE (country.id=people2.country) AND " .
    "(people2.id='$user')";
if (!$result = mysql_query($sql, $db)) {
  error_log(mysql_error($db));
}
$row = mysql_fetch_array($result);
if (!$row) {
  HtmlHead("userview", "User not found", $userstatus, $userid);
  echo "<h3>Sorry, user not found</h3>\n";
  echo "<br>\n";
  HtmlTail();
  exit();
}
$firstname = $row["firstname"];
$surname = $row["surname"];
$city = $row["city"];
$name = $row["name"];
$arrival = $row["arrival"];
$departure = $row["departure"];
$attending = $row["attending"];
$children = $row["children"];
$travelby = $row["travelby"];
$status = $row["status"];
$kindofaccomodation = $row["kindofaccomodation"];
$nameofaccomodation = $row["nameofaccomodation"];
$logon = $row["logon"];
$email = $row["email"];

HtmlHead("userview", $firstname . " " . $surname, $userstatus, $userid);

$sql = "SELECT whois,galpix FROM people2,whois WHERE (people2.id='$user') " .
    "AND (people2.id=whois.lbwid)";
if (!$result = mysql_query($sql, $db)) {
  error_log(mysql_error($db));
}
$pix = mysql_fetch_array($result);

if ($userid == $user || $userstatus > 8) {
  echo "<table class='reginfo'>";
  echo "<tr>";
  echo "<th>";
  echo "<A href=useredit.php?user=$user>[Edit this Entry]</a>";
  echo "</td>";
  echo "</tr>";
  echo "</table>\n";
}

echo "<table class='reginfo'>\n";
echo "<tr>";
echo "<TH COLSPAN=3>$firstname $surname</th>";
echo "</tr>\n";
echo "<tr>";
echo "<td>City</td>";
echo "<td>$city</td>";
$picture = ($pix["whois"] > 0) ?
    "<img src='pictures/" . $pix["galpix"] . "'>"
    : "<br>No<br>Picture<br>Available<br>";
echo "<td rowspan='8'>";
echo $picture;
echo "</td></tr>\n";
echo "<tr><td>Country</td><td>$name</td></tr>\n";
echo "<tr><td>Arriving</td><td>" . $date[$arrival] . "</td></tr>\n";
echo "<tr><td>Leaving</td><td>" . $date[$departure] . "</td></tr>\n";
printf("<tr><td>Attending</td><td>%s</td></tr>\n", $attending ? "Yes" : "No");
echo "<tr><td>Children</td><td>$children</td></tr>\n";
echo "<tr><td>Travelling by</td><td>" . $xport[$travelby] . "</td></tr>\n";
if ($userstatus > 8) {
  echo "<tr><td>Status</td><td>$status</td></tr>\n";
  echo "<tr><td>Login</td><td>" . htmlspecialchars($logon) . "</td></tr>\n";
}
if (($userstatus > 2) || ($user == $userid)) {
  echo "<tr><td>E-Mail</td><td><A href=mailto:" . htmlspecialchars($email) . ">" . htmlspecialchars($email) . "</a></td></tr>";
}
$accomodationtype = $acctype[$kindofaccomodation];
$accomodationname = (strlen($nameofaccomodation) > 2) ? $nameofaccomodation : "?";
printf("<tr><td colspan='3'> Accomodation Type:&nbsp;%s&nbsp;&nbsp;Name:&nbsp;%s</td></tr>\n", htmlspecialchars($accomodationtype), htmlspecialchars($accomodationname));
echo "</table>";
echo "<br>\n";

if ($userstatus > 8) {
  ?>
  <form method='post' action='upgrade.php'>
    <input type='hidden' name='lbwid' value='<?php echo $user ?>'/>
    <input type='submit' name='action' value='mark present' class='adminbar' style="width: auto"/>
    <input type='submit' name='action' value='upgrade' class='adminbar' style="width: auto"/>
    <input type='submit' name='action' value='downgrade' class='adminbar' style="width: auto"/>
    <input type='submit' name='action' value='remove' class='adminbar' style="width: auto"/>
  </form>
<?php
}

echo "<br>\n";
HtmlTail();
?>
