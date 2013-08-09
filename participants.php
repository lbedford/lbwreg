<?php
require("basefunc.inc.php");

/* variables from the environment (GET/POST) */
if (array_key_exists("order", $_REQUEST)) {
  $order = addslashes($_REQUEST["order"]);
}

session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

$db = ConnectMysql();

HtmlHead("participants", "Participants", $_SESSION["userstatus"], $_SESSION["userid"]);

echo "";

if (!isset($order) or ($order == "")) {
  $order = "surname";
}

$ordering = "ASC";
if ($order == "present") {
  $ordering = "DESC";
}

$query = "SELECT  people2.id as id, firstname, surname, email, " .
    "city, country.name as country, arrival, departure, attending, " .
    "children, kindofaccomodation, present FROM people2,country " .
    "where (attending>0) AND (country.id = people2.country)  AND " .
    "(status>1)  ORDER by $order $ordering";
$result = mysql_query($query, $db);
if (!$result) {
  printf("%s<br>%s<br>", $query, mysql_error($db));
}

global $date, $acctype;
$r2 = mysql_query("SELECT Count(*) AS count,sum(attending) AS geeks,sum(children)AS kids FROM people2 WHERE (attending>0) AND (status>1)", $db);
echo "<table class='reginfo'  cellpadding=1 >\n";
$totals = mysql_fetch_array($r2);
printf("<tr ><TH COLSPAN=6 ALIGN=CENTER>Registered Users (%d registrations; %d adults and %d children):</th></tr>\n", $totals["count"], $totals["geeks"], $totals["kids"]);
echo "<tr ><th><A href='?order=surname'>Name</a></th>";
echo "<th><A href='?order=city,surname'>City</a>,&nbsp;&nbsp;<A href='?order=country,city,surname'>Country</a></th><th>Adults<br>Children</th><th>Dates</th><th><A href='?order=kindofaccomodation'>Accomodation</a></th><th><a href='?order=present'>Present</a></tr>\n";
while ($row = mysql_fetch_array($result)) {
  printf("<tr><td>");
  printf("<A HREF=userview.php?user=%d>", $row["id"]);
  printf("%s, %s</a>", $row["surname"], $row["firstname"]);

  printf("</td><td>%s, %s</td>", $row["city"], $row["country"]);
  printf("<td>%d + %d</td>", $row["attending"], $row["children"]);
  printf("<TD align=center> %s - %s</a></td>", $date[$row["arrival"]], $date[$row["departure"]]);
  printf("<td>%s</td>\n", $acctype[$row["kindofaccomodation"]]);
  printf("<td>%s</td></tr>\n", $row["present"] ? "Yes" : "No");
}

echo "</table><br>";

$sql = "SELECT country , count(*) AS q,sum(attending) AS number,sum(children) AS kids,name  FROM people2, country WHERE (country.id = people2.country) AND (attending>0) AND (status>=2)   GROUP BY country ORDER BY number DESC";
$result = mysql_query($sql, $db);

if (!$result)
  printf("%s<br>\n", mysql_error($db));
else {
  printf("<table class='reginfo' CELLPADDING=1 ><tr ><TH COLSPAN=4>By Country</th></tr><tr ><th>Country</th><th>Entries</th><th>Adults</th><th>Children</th></tr>\n");
  while ($row = mysql_fetch_array($result)) {
    printf("<tr><td> %s </td><TD align=center> %s </td><TD align=center> %s </td><TD align=center> %s</td></tr>\n", $row["name"], $row["q"], $row["number"], $row["kids"]);
  }
  printf("</table>\n");
}

HtmlTail();
