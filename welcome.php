<?php
require("basefunc.inc.php");

$javascript = "<script type='text/javascript' src='http://www.google.com/jsapi'></script>\n" .
    "<script type='text/javascript'>\n" .
    "google.load('visualization', '1', {'packages': ['intensitymap']});\n</script>\n";
/* variables from the environment (GET/POST) */

$drawmap_header = "<script type='text/javascript'>\n" .
    "google.setOnLoadCallback(drawMap);\n" .
    "function drawMap() {\n" .
    "var data = new google.visualization.DataTable();\n";

$drawmap_footer = "var options = {};\n" .
    "var container = document.getElementById('map_canvas');\n" .
    "var map = new google.visualization.IntensityMap(container);\n" .
    "map.draw(data, options);\n" .
    "options.region = 'europe';\n" .
    "container = document.getElementById('map_canvas2');\n" .
    "map = new google.visualization.IntensityMap(container);\n" .
    "map.draw(data, options);\n};\n" .
    "</script>\n";

session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

header("Pragma: no-cache");
$db = ConnectMysql();

$_SESSION["userforum"] = 1;

$query = "SELECT * FROM people2 WHERE id='" . $_SESSION["userid"] . "'";
$result = mysql_query($query, $db);
$user_row = mysql_fetch_array($result);
$_SESSION["userstatus"] = intval($user_row["status"]);

HtmlHead("welcome", "LBW $year welcomes " . $_SESSION["username"], $_SESSION["userstatus"], $_SESSION["userid"], $javascript);

include("motd.html");

//time to go
$started = 0;
$togo = mktime(9, 0, 0, $month, $day, $year) - time();
if ($togo < 0) {
  $started = 1;
  $togo = $togo * -1;
}
$mintogo = intval($togo / 60);
$secs = $togo - ($mintogo * 60);
$hours = intval($mintogo / 60);
$mins = $mintogo - 60 * $hours;
$days = intval($hours / 24);
$hours -= $days * 24;
if ($started == 0) {
  printf("<b>Time till the LBW starts: %d days %d hours %d mins<br /></b>", $days, $hours, $mins);
} else {
  printf("<b>Time since the LBW started: %d days %d hours %d mins<br /></b>", $days, $hours, $mins);
  $result = mysql_query("SELECT count(*) as regs,sum(attending) as ads, sum(children)as kids, count(distinct country) as countries " .
  "FROM people2 where (attending>0) AND (status>1) " .
  "AND (present = 1)", $db);
  $row = mysql_fetch_array($result);

  $article = "are";
  if ($row["regs"] == 1) {
    $article = "is";
  }

  printf("<b>There $article %d adults and %d children present, from %d countries.<br /></b>\n",
    $row["ads"], $row["kids"], $row["countries"]);
}

// Quick statistics
echo "<br />";
$result = mysql_query("SELECT count(*) as regs,sum(attending) as ads, sum(children)as kids, count(distinct country) as countries " .
"FROM people2 where (attending>0) AND (status>1) " .
"AND arrival IS NOT NULL AND departure IS NOT NULL", $db);
$row = mysql_fetch_array($result);

$article = "are";
$extension = "s";
if ($row["regs"] == 1) {
  $article = "is";
  $extension = "";
}
printf("There $article %d registration$extension, totalling %d adults and %d children from %d countries, who really know where their towels are.<br>\n",
  $row["regs"], $row["ads"], $row["kids"], $row["countries"]);

$result = mysql_query("SELECT count(*) as regs,sum(attending) as ads, sum(children)as kids, count(distinct country) as countries " .
"FROM people2 where (attending>0) AND (status>1) " .
"AND (arrival IS NULL OR departure IS NULL)", $db);
$row = mysql_fetch_array($result);
if ($row["regs"] > 0) {
  $article = "are";
  $extension = "s";
  if ($row["regs"] == 1) {
    $article = "is";
    $extension = "";
  }
  printf("There $article %d indecisive registration$extension, totalling %d adults and %d children from %d countries," .
  " who don't know when they're coming or going<br>\n", $row["regs"], $row["ads"], $row["kids"], $row["countries"]);
}
echo "<hr /><br />";

global $date, $shortday;

// Personal greeting;
echo "<b>Welcome, " . $user_row["firstname"] . "!</b><br /><br />";
if ($user_row["attending"] < 1) {
  echo "We don't know if you're joining us this year.<br>";
  echo "If you can come please <a href='useredit.php'>update your user record</a>. set \"attending\" or more and enter your dates<br>";
} else {
  if (!is_null($user_row["arrival"]) && !is_null($user_row["departure"]) && ($user_row["attending"] > 0)) {
    printf("<b>You are expected here from %s until %s.<br />\n", $date[$user_row["arrival"]], $date[$user_row["departure"]]);
    echo("If this is incorrect please <a href='useredit.php'>update your registry  entry</a>.<br />\n");
  } else {
    $v = "Your ";
    $c = 0;
    if (is_null($user_row["arrival"])) {
      $v .= "arrival ";
      $c++;
    }
    if (is_null($user_row["departure"])) {
      if ($c == 1)
        $v .= "and ";
      $v .= "departure ";
      $c++;
    }
    if ($c > 1) $v .= "dates are unknown";
    else $v .= "date is unknown";
    echo "<b>$v!<br>\n";
    echo "Please<a href='useredit.php'> update your registration records </a>so that we can schedule events for maximum attendance<br>\n";
    echo "Thank you<br>\n";
  }
}
echo "<br />";

//Presenting
$result = mysql_query(
  "SELECT name,type, messages, id as forum,type,day,hour,forum_duration " .
  "FROM Events WHERE owner='" . $_SESSION["userid"] .
  "' ORDER BY day,hour", $db);
$q = mysql_num_rows($result);
if ($q) {
  echo "<table class='reginfo'>";
  echo "<tr>\n";
  echo "<th colspan=4>You are responsible for these events</th>\n";
  echo "</tr>\n";
  echo "<tr>\n";
  echo "<th>Title</th>\n";
  echo "<th>Subs.</th>\n";
  echo "<th>Msgs.</th>\n";
  echo "<th>Schedule</th>";
  echo "</tr>";
  while ($row = mysql_fetch_array($result)) {
    if ($row["type"] == 1)
      $sched = "All Week";
    else {
      $evday = $row["day"];
      if ($evday < 1)
        $sched = "Not yet scheduled";
      else {
        $hr = $row["hour"];
        $end = $hr + $row["forum_duration"];
        $sched = $shortday[$evday] . " " .
            $date[$evday] . ": &nbsp; " . $hr . ":00 - " . $end . ":00";
      }
    }
    echo "<tr>\n";
    echo "<td>\n";
    echo "<span style='text-align: left;'><a href='forum.php?forum=" . $row["forum"] . "'>" .
        stripslashes($row["name"]) . "</a></span></td>\n";
    echo "<td>";
    $event_sub_result = mysql_query(
      "SELECT * FROM eventreg WHERE (event='" .
      $row["forum"] . "') AND (geek !='" .
      $_SESSION["userid"] . "')",
      $db);
    echo mysql_num_rows($event_sub_result);
    echo "</td>\n";
    echo "<td>" . $row["messages"] . "</td>\n";
    echo "<td>" . $sched . "</td>\n</tr>\n";
  }
  echo "</table><br />";
}

// Registered for:

$result = mysql_query("SELECT name, firstname ,surname, messages,Events.id as evt, owner,type,day,hour,forum_duration FROM Events, people2, eventreg WHERE (Events.id = eventreg.event) AND (people2.id = Events.owner) and ((eventreg.geek='" . $_SESSION["userid"] . "') AND (geek != owner))order by day,hour,forum_duration", $db);
if (!$result)
  printf("%s<br>", mysql_error($db));
$q = mysql_num_rows($result);
If (($q > 0) || ($_SESSION["userstatus"] > 2)) {
  echo "<table class='reginfo' border='1'>";
  echo "<TR><TH COLSPAN=5><span style='text-align: center;'>You are registered for $q <a href='activities.php'>events</a></span></th></tr>";
  echo "<tr ><th>Event</th><th>Co-ordinator</th><th>Subs.</th><th>Msgs.</th><th>Schedule</th></tr>";
  global $timestamps;
  while ($row = mysql_fetch_array($result)) {
    $owner = $row["owner"];
    if ($row["type"] == 1)
      $sched = "All Week";
    else {
      $evday = $row["day"];
      if ($evday < 1)
        $sched = "Not yet scheduled";
      else {
        $hr = $row["hour"];
        $starttimestamp = $timestamps[$evday] + ($hr * 3600);
        $endtimestamp = $starttimestamp + ($row["forum_duration"] * 3600);
        $endformat = "H:i";
        if (date("l", $starttimestamp) != date("l", $endtimestamp)) {
          $endformat = "l " . $endformat;
        }
        $end = $hr + $row["forum_duration"];
        $sched = date("l j F H:i", $starttimestamp) . " - " . date($endformat, $endtimestamp);
      }
    }
    echo "<tr>";
    echo "<td>";
    printf("<A href='forum.php?forum=%d'>%s</a>", $row["evt"], $row["name"]);
    echo "</td>";
    echo "<td>";
    printf("<a href='userview.php?user=%d'>%s %s</a>",
      $row["owner"], $row["firstname"], $row["surname"]);
    echo "</td>";
    echo "<TD>";
    echo mysql_num_rows(mysql_query("SELECT * FROM eventreg WHERE (event=" . $row["evt"] . ") AND  (geek != $owner )"));
    echo "</td>";
    echo "<TD>";
    echo $row["messages"];
    echo "</td>";
    echo "<TD>";
    echo $sched;
    echo "</td>";
    echo "</tr>";
  }
  echo "</table><br />";
}


// Message board

//echo "<A href='messages.php'></a>";
$sql = "SELECT discussions.id AS mid, firstname, surname, subject, posted FROM discussions, people2 WHERE (people2.id=writer)  AND (forum = 1) ORDER BY posted";
$result = mysql_query($sql, $db);
$count = mysql_num_rows($result);
echo "<table class='reginfo' border='1'>";
printf("<tr><td colspan='4'><span style='text-align: center; text-emphasis: bold;'>Message Board<br>%d Messages in Open Forum<br>%s</span></td></tr>\n",
  $count,
  ($_SESSION["userstatus"] > 2) ? "<A href='messages.php?submit=write'>Post a message</a>" :
      "&nbsp;");
if ($count) {
  echo "<tr><th class='from'>From</th><th class='subject'>Subject</th><th>Time</th><th>&nbsp;</th></tr>";
  while ($msg = mysql_fetch_array($result)) {
    printf("<tr><td>%s %s</td><td>%s</td><td>%s</td><td><a href='messages.php?submit=read&number=%d'>Read</a></td></tr>\n",
      $msg["firstname"], $msg["surname"], stripslashes($msg["subject"]), $msg["posted"], $msg["mid"]);
  }
}
echo("</table>");

$sql = "SELECT sum(people2.attending + people2.children) AS total,country.code FROM people2,country WHERE country.id = people2.country AND people2.attending > 0 AND people2.status > 1 GROUP BY country.name;";
$result = mysql_query($sql, $db);
$count = mysql_num_rows($result);
$drawmap_body = "data.addRows($count);\n" .
    "data.addColumn('string', 'Country');\n" .
    "data.addColumn('number', 'Attendees');\n";
if ($count) {
  $counter = 0;
  while ($msg = mysql_fetch_array($result)) {
    $drawmap_body .= "data.setValue($counter, 0, '" . $msg["code"] . "');\n";
    $drawmap_body .= "data.setValue($counter, 1, $year);\n";
    $counter++;
  }
}


echo "$drawmap_header";
echo "$drawmap_body";
echo "$drawmap_footer";
echo "<div id='map_canvas' style='text-align: center;'></div>\n";
echo "<div id='map_canvas2' style='text-align: center;'></div>\n";
HtmlTail();