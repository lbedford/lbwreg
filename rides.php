<?php
require("basefunc.inc.php");

/* variables from the environment (GET/POST) */
/* none found */

$_SESSION["userid"] = 0;
session_start();
if (!$_SESSION["userid"]) {
  header("Location: login.php");
  exit();
}

header("Pragma: no-cache");
$db = ConnectMysql();

HtmlHead("rides", "Ride Sharing", $_SESSION["userstatus"], $_SESSION["userid"]);
echo "";
echo "<h2>Rides Offered</h2>";

//RIDE TO OFFERS

echo "<table class='reginfo' WIDTH=95%  >";
echo "<tr ><TH COLSPAN=6>Rides To $location</th></tr>";
echo "<tr><th width=25%>From</th><th width=15%>Date</th><th width=10%>Places</th><th width=25%>Offered by</th><th width=20%>Notes</th><th>&nbsp;</th></tr>";

$sql = "SELECT rides.id as ride,orig,date,space,notes,person,rides.email as mailto,firstname,surname FROM rides, people2 where (rides.person=people2.id) AND (dest='" . $location . "') AND (type='offer')";
$result = mysql_query($sql, $db);
if (!$result) {
  printf("%s<br>%s<br>", $sql, mysql_error($db));
  exit();
}
while ($row = mysql_fetch_array($result))
  printf("<tr><td>%s</td><td>%s</td><td>%s</td><td><a href=mailto:%s>%s %s</a></td><td>%s</td><td>%s</td></tr>",
    $row["orig"], $row["date"], $row["space"], $row["mailto"], $row["firstname"], $row["surname"], $row["notes"], ($_SESSION["userid"] == $row["person"]) ? "<A HREF=rideform.php?option=edit&ride=" . $row["ride"] . ">[Ed]</a>" : "&nbsp;");
echo "<tr ><TD COLSPAN=6 ALIGN=CENTER><FORM METHOD=POST ACTION=rideform.php>";
echo "<INPUT TYPE=HIDDEN NAME=dir VALUE=\"TO\"><INPUT TYPE=HIDDEN NAME=xtype VALUE=\"offer\">";
echo "<INPUT TYPE=SUBMIT NAME=option VALUE=\"Offer a Ride\"></form></td></tr>";
echo "</table>";

//RIDE FROM OFFERS
echo "<br />";
echo "<table class='reginfo' WIDTH=95%  >";
echo "<tr ><TH COLSPAN=6>Rides From $location</th></tr>";
echo "<tr><th width=25%>To</th><th width=15%>Date</th><th width=10%>Places</th><th width=25%>Offered by</th><TH WIDth=20%>Notes</th><th>&nbsp;</th></tr>";

$sql = "SELECT rides.id as ride,person,dest,date,space,notes,rides.email as mailto,firstname,surname FROM rides, people2 where (rides.person=people2.id) AND (orig='" . $location . "') AND (type='offer')";

$result = mysql_query($sql, $db);
if (!$result) {
  printf("%s<br>%s<br>", $sql, mysql_error($db));
  exit();
}
while ($row = mysql_fetch_array($result))
  printf("<tr><td>%s</td><td>%s</td><td>%s</td><td><a href=mailto:%s>%s %s</a></td><td>%s</td><td>%s</td></tr>",
    $row["dest"], $row["date"], $row["space"], $row["mailto"], $row["firstname"], $row["surname"], $row["notes"], ($_SESSION["userid"] == $row["person"]) ? "<A HREF=rideform.php?option=edit&ride=" . $row["ride"] . ">[Ed]</a>" : "&nbsp;");
echo "<tr ><TD COLSPAN=6 ALIGN=CENTER><FORM METHOD=POST ACTION=rideform.php>";
echo "<INPUT TYPE=HIDDEN NAME=dir VALUE=\"FROM\"><INPUT TYPE=HIDDEN NAME=xtype VALUE=\"offer\">";
echo "<INPUT TYPE=SUBMIT NAME=option VALUE=\"Offer a Ride\"></form></td></tr>";
echo "</table>";

echo "<br><h2>Rides Wanted</h2>";

//RIDE TO REQUESTS

echo "<table class='reginfo' WIDTH=95%  >";
echo "<tr ><TH COLSPAN=6>Rides To $location</th></tr>";
//echo "<tr><th>FROM</th><th>DATE</th><th>Places</th><th>Wanted by</th><th>Notes</th></tr>";
echo "<tr><th width=25%>From</th><th width=15%>Date</th><th width=10%>Places</th><th width=25%>Wanted by</th><th width=20%>Notes</th><th>&nbsp;</th></tr>";


$sql = "SELECT rides.id as ride,person,orig,date,space,notes,rides.email as mailto,firstname,surname FROM rides, people2 where (rides.person=people2.id) AND (dest='" . $location . "') AND (type='request')";
$result = mysql_query($sql, $db);
if (!$result) {
  printf("%s<br>%s<br>", $sql, mysql_error($db));
  exit();
}
while ($row = mysql_fetch_array($result))
  printf("<tr><td>%s</td><td>%s</td><td>%s</td><td><a href=mailto:%s>%s %s</a></td><td>%s</td><td>%s</td></tr>",
    $row["orig"], $row["date"], $row["space"], $row["mailto"], $row["firstname"], $row["surname"], $row["notes"], ($_SESSION["userid"] == $row["person"]) ? "<A HREF=rideform.php?option=edit&ride=" . $row["ride"] . ">[Ed]</a>" : "&nbsp;");
echo "<tr ><TD COLSPAN=6 ALIGN=CENTER><FORM METHOD=POST ACTION=rideform.php>";
echo "<INPUT TYPE=HIDDEN NAME=dir VALUE=\"TO\"><INPUT TYPE=HIDDEN NAME=xtype VALUE=\"request\">";
echo "<INPUT TYPE=SUBMIT NAME=option VALUE=\"Request a Ride\"></form></td></tr>";
echo "</table>";


//RIDE FROM REQUESTS
echo "<table class='reginfo' WIDTH=95%  >";
echo "<tr ><TH COLSPAN=6>Rides From $location</th></tr>";
//echo "<tr><th>TO</th><th>DATE</th><th>Places</th><th>Wanted by</th><th>Notes</th><th>&nbsp;</TH</tr>";
echo "<tr><th width=25%>To</th><th width=15%>Date</th><th width=10%>Places</th><th width=25%>Wanted by</th><th width=20%>Notes</th><th>&nbsp;</th></tr>";

echo "<br />";
$sql = "SELECT rides.id as ride,person,dest,date,space,notes,rides.email as mailto,firstname,surname FROM rides, people2 where (rides.person=people2.id) AND (orig='" . $location . "') AND (type='request')";
$result = mysql_query($sql, $db);
if (!$result) {
  printf("%s<br>%s<br>", $sql, mysql_error($db));
  exit();
}
while ($row = mysql_fetch_array($result))
  printf("<tr><td>%s</td><td>%s</td><td>%s</td><td><a href=mailto:%s>%s %s</a></td><td>%s</td><td>%s</td></tr>",
    $row["dest"], $row["date"], $row["space"], $row["mailto"], $row["firstname"], $row["surname"], $row["notes"], ($_SESSION["userid"] == $row["person"]) ? "<A HREF=rideform.php?option=edit&ride=" . $row["ride"] . ">[Ed]</a>" : "&nbsp;");
echo "<tr ><TD COLSPAN=6 ALIGN=CENTER><FORM METHOD=POST ACTION=rideform.php>";
echo "<INPUT TYPE=HIDDEN NAME=dir VALUE=\"FROM\"><INPUT TYPE=HIDDEN NAME=xtype VALUE=\"request\">";
echo "<INPUT TYPE=SUBMIT NAME=option VALUE=\"Request a Ride\"></form></td></tr>";
echo "</table>";

HtmlTail();