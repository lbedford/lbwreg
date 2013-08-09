<?php
include("basefunc.inc.php");


function GetAnnotatedName($name, $potential_attendees, $attendees, $conflicts)
{
  if ($attendees == 0) {
    return $name;
  }
  $span_class = 'normal';
  if ($potential_attendees != $attendees) {
    $span_class = 'missing_attendees';
  }
  return "<span class='$span_class'>$name ($conflicts/$attendees)</span>";
}

function RunMysqlQuery($db, $query)
{
  $result = mysql_query($query, $db);
  if (!$result) {
    error_log("Query failed: $query");
    return 0;
  }
  $val = mysql_fetch_row($result)[0];
  return !is_null($val) ? $val : 0;
}

function CalculatePotentialAttendees($db, $event_id)
{
  $query = "SELECT COUNT(people2.id) from people2, Events, eventreg " .
      "where people2.id = eventreg.geek and Events.id = eventreg.event " .
      "and Events.day < people2.departure and Events.day > people2.arrival " .
      "and eventreg.event=$event_id";
  return RunMysqlQuery($db, $query);
}

function CalculateRegistered($db, $event_id)
{
  $query = "SELECT COUNT(geek) FROM eventreg WHERE event=$event_id";
  return RunMysqlQuery($db, $query);
}

function CalculateConflicts($db, $event_one, $event_two)
{
  if ($event_one == 0 || $event_two == 0) {
    return 0;
  }
  $query = "SELECT COUNT(e1.geek) FROM eventreg as e1, eventreg as e2 " .
      "WHERE e1.geek=e2.geek AND e1.event=$event_one and " .
      "e2.event=$event_two";
  return RunMysqlQuery($db, $query);
}

function CalculateDepartures($db, $day)
{
  // total up the number of people departing today
  $query = "SELECT SUM(attending+children) as deps " .
      "FROM people2 where departure = $day";
  return RunMysqlQuery($db, $query);
}

function CalculateArrivals($db, $day)
{
  // total up the number of people arriving today
  $query = "SELECT SUM(attending+children) as arrs " .
      "FROM people2 where arrival = $day";
  return RunMysqlQuery($db, $query);
}

function CalculateAttendees($db, $day)
{
  $query = "SELECT SUM(attending + children) as val " .
      "FROM people2 where arrival <= $day AND " .
      "departure > $day";
  return RunMysqlQuery($db, $query);
}

/* variables from the environment (GET/POST) */
/* (none found) */

$_SESSION["userid"] = 0;
session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

$db = connectMysql();

HtmlHead("schedule.php", "Provisional Schedule",
  $_SESSION["userstatus"], $_SESSION["userid"]);

if ($_SESSION["userstatus"] == 16) {
  echo "Numbers in brackets after event names indicate:<br>";
  echo "(number of people with conflicts / number of participants)<br>";
  echo "Conflicts are only counted for events further to the right.<br>";
} else {
  echo "Numbers in brackets after events names" .
      " indicate the number of participants.<br>";
}

global $date, $weekday;

for ($day = 1; $day < count($date) - 2; $day++) {
  $evid = array();
  $name = array();
  $hour = array();
  $sched = array();
  $attendees = array();
  $conflicts = array();
  $potential = array();
  $rowspan = array();


  $event_sql = "SELECT id,schedtxt,hour,forum_duration,type,tslot " .
      "FROM Events where (day=$day) and type != 1 ORDER BY hour,forum_duration,tslot";
  $result = mysql_query($event_sql, $db);
  $name[0] = "&nbsp;";
  $name[99] = "<span class='conflict'>*** Conflict ***</span>";
  $evt = 0;
  for ($i = 0; $i < 72; $i++) {
    // initialize every hour
    $evid[$i] = 0;
    $sched[$i] = 0;
    $conflicts[$i] = 0;
    $attendees[$i] = 0;
    $potential[$i] = 0;
    $rowspan[$i] = 1;
  }
  while ($row = mysql_fetch_array($result)) {
    $slot = 0;
    $evt++;
    $h = $hour[$evt] = $row["hour"];
    $name[$evt] = "<a href=forum.php?forum=" .
        $row["id"] . ">" . $row["schedtxt"] . "</a>";
    $evid[$evt] = $row["id"];
    $dur = $rowspan[$evt] = $row["forum_duration"];

    $attendees[$evt] = CalculateRegistered($db, $row["id"]);
    $potential[$evt] = CalculatePotentialAttendees($db, $row["id"]);

    if ($dur < 3) $slottype = 1; // One hour slot(s)
    else if (($dur == 5) && ($h != 9) && ($h != 13))
      $slottype = 3; // Half day afternoon, or whole day
    else if ($dur < 6) $slottype = 2; // Half day slot
    else           $slottype = 4; // Whole day slot

    switch ($slottype) {
      case 1: // find the right 1 hour slot
        $slot = $h;
        break;
      case 2: // find the right 1/2 day slot
        $slot = $h + 24;
        break;
      case 3: // half afternoon, or whole day
        if ($h < 13)
          $slot = $h + 48;
        else
          $slot = $h + 24;
        break;
      case 4: // it's a full day
        $slot = $h + 48;
        break;
    }

    // if there's something in this slot already
    // mark it as a conflict
    if ($sched[$slot])
      $sched[$slot] = 99;
    else
      $sched[$slot] = $evt;

    if ($dur > 1) {
      for ($offset = 1; $offset < $dur; $offset++) {
        $sched[$slot + $offset] = $evt;
      }
    }
  };

  echo "<br>";

  $arr = CalculateArrivals($db, $day);
  $dep = CalculateDepartures($db, $day);
  $att = CalculateAttendees($db, $day);

  echo "<table class='reginfo' width=100% ><tr ><th colspan=4>" .
      $weekday[$day] . " " . $date[$day] . " ($arr arrival";
  if ($arr != 1) echo "s";
  echo "; $dep departure";
  if ($dep != 1) echo "s";
  echo "; $att attendees)</th></tr>\n";
  echo "<tr ><th width=10%>Time</th>\n";
  echo "<th width=30%>Short events</th>\n";
  echo "<th width=30%>Half day events</th>\n";
  echo "<th width=30%>Day long events</th></tr>";

  for ($evnum = 0; $evnum < 48; $evnum++) {
    $e1 = $evid[$sched[$evnum]];
    $e2 = $evid[$sched[$evnum + 24]];
    $conflicts[$sched[$evnum]] = max($conflicts[$sched[$evnum]],
      CalculateConflicts($db, $e1, $e2));
  }

  $output_string_array = array();
  for ($day_hour = 0; $day_hour < 9; $day_hour++) {
    if ($sched[$day_hour] != 0) {
      $output_string_array[$day_hour] = GetAnnotatedName(
        $name[$sched[$day_hour]], $potential[$sched[$day_hour]],
        $attendees[$sched[$day_hour]], $conflicts[$sched[$day_hour]]);
    }
  }
  if (count($output_string_array) != 0) {
    echo "<tr><td>before 0900</td><td align=center colspan=3>" .
        implode("<br>", $output_string_array) . "</td></tr>\n";
  }

  for ($day_hour = 9; $day_hour < 24; $day_hour++) {
    if ($day_hour > 17) {
      if (($sched[$day_hour] == 0) && ($sched[$day_hour + 24] == 0) && ($sched[$day_hour + 48] == 0)) {
        continue;
      }
    }
    echo "<tr><td>";
    printf("%02d:00-%2d:00</td>", $day_hour, $day_hour + 1);
    if ($sched[$day_hour] == 0) {
      echo "<td></td>";
    } else {
    }
    if ($sched[$day_hour] != $sched[$day_hour - 1]) {
      $output_string = GetAnnotatedName($name[$sched[$day_hour]],
        $potential[$sched[$day_hour]], $attendees[$sched[$day_hour]],
        $conflicts[$sched[$day_hour]]);
      echo "<td align=center rowspan=" . $rowspan[$sched[$day_hour]] . ">";
      echo $output_string;
      echo "</td>";
    }
    $middle_hour = $day_hour + 24;
    if ($sched[$middle_hour] == 0) {
      echo "<td></td>";
    } else {
      if ($sched[$middle_hour] != $sched[$middle_hour - 1]) {
        $middle_output_string = GetAnnotatedName($name[$sched[$middle_hour]],
          $potential[$sched[$middle_hour]], $attendees[$sched[$middle_hour]],
          $conflicts[$sched[$middle_hour]]);

        echo "<TD rowspan=" . $rowspan[$sched[$middle_hour]] . " align=center>$middle_output_string</td>";
      }
    }
    $long_hour = $day_hour + 48;
    if ($sched[$long_hour] == 0) {
      echo "<td></td>";
    } else {
      if ($sched[$long_hour] != $sched[$long_hour - 1]) {
        $long_output_string = GetAnnotatedName($name[$sched[$long_hour]],
          $potential[$sched[$long_hour]], $attendees[$sched[$long_hour]],
          $conflicts[$sched[$long_hour]]);

        echo "<TD rowspan=" . $rowspan[$sched[$long_hour]] . " align=center>$long_output_string</td>";
      }
    }
  }

  printf("</table>\n");
}
HtmlTail();