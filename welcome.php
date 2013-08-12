<?php
require("basefunc.inc.php");

function CalculateTimeToSinceLbw($year, $month, $day)
{
  // Calculate time since/until LBW starts.
  $lbw_started = 0;
  $togo = mktime(9, 0, 0, $month, $day, $year) - time();
  if ($togo < 0) {
    $lbw_started = 1;
    $togo = $togo * -1;
  }
  $mintogo = intval($togo / 60);
  $hours = intval($mintogo / 60);
  $mins = $mintogo - 60 * $hours;
  $days = intval($hours / 24);
  $hours -= $days * 24;
  return Array('lbw_started' => $lbw_started,
    'time_to_since_lbw' => "$days days, $hours hours, $mins mins");
}

session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

header("Pragma: no-cache");
$db = ConnectMysql();

$_SESSION["userforum"] = 1;

$query = "SELECT status, firstname, surname, attending, arrival, departure FROM people2 WHERE id='" . $_SESSION["userid"] . "'";
$result = mysql_query($query, $db);
if (!$result) {
  error_log(mysql_error($db));
}
$user_row = mysql_fetch_array($result);
$_SESSION["userstatus"] = intval($user_row["status"]);

if ($_SESSION["userstatus"] > 8) {
  $admin = 1;
}

global $date, $shortday, $timestamps;

$template_details = CalculateTimeToSinceLbw($year, $month, $day);

$template_details['year'] = $year;
$template_details['location'] = $location;
$template_details['start_date'] = $date[1];
$template_details['end_date'] = $date[count($date) - 3];
$result = mysql_query("SELECT count(*) as regs,sum(attending) as ads, sum(children)as kids, count(distinct country) as countries " .
"FROM people2 where (attending>0) AND (status>1) " .
"AND (present = 1)", $db);
$row = mysql_fetch_array($result);

$template_details['present_adults'] = $row["ads"];
$template_details['present_children'] = $row["kids"];
$tempate_details['present_countries'] = $row["countries"];

// Quick statistics
$result = mysql_query("SELECT count(*) as regs,sum(attending) as ads, sum(children)as kids, count(distinct country) as countries " .
"FROM people2 where (attending>0) AND (status>1) " .
"AND arrival IS NOT NULL AND departure IS NOT NULL", $db);
$row = mysql_fetch_array($result);

$template_details['potential_registrations'] = $row["regs"];
$template_details['potential_adults'] = $row["ads"];
$template_details['potential_kids'] = $row["kids"];
$template_details['potential_countries'] = $row["countries"];

$result = mysql_query("SELECT count(*) as regs,sum(attending) as ads, sum(children)as kids, count(distinct country) as countries " .
"FROM people2 where (attending>0) AND (status>1) " .
"AND (arrival IS NULL OR departure IS NULL)", $db);
$row = mysql_fetch_array($result);

$template_details['indecisive_registrations'] = $row["regs"];
$template_details['indecisive_adults'] = $row["ads"];
$template_details['indecisive_kids'] = $row["kids"];
$template_details['indecisive_countries'] = $row["countries"];

$template_details['firstname'] = $user_row["firstname"];
$template_details['attending'] = $user_row["attending"];

$template_details['arrival_date'] = $date[$user_row["arrival"]];
$template_details['departure_date'] = $date[$user_row["departure"]];

$template_details['unknown_dates'] = 0;
if (is_null($user_row["arrival"]) || is_null($user_row["departure"]) && ($user_row["attending"] > 0)) {
  $template_details['unknown_dates'] = 1;
}

//Presenting
$result = mysql_query(
  "SELECT name,type, messages, id as forum,type,day,hour,forum_duration " .
  "FROM Events WHERE owner='" . $_SESSION["userid"] .
  "' ORDER BY day,hour", $db);
$q = mysql_num_rows($result);
$template_details['org_events'] = Array();
if ($q) {
  while ($row = mysql_fetch_array($result)) {
    $org_event = Array();
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
            $date[$evday] . ": " . $hr . ":00 - " . $end . ":00";
      }
    }
    $org_event['schedule_text'] = $sched;
    $org_event['id'] = $row['forum'];
    $org_event['name'] = $row['name'];
    $event_att_result = mysql_query(
      "SELECT * FROM eventreg WHERE (event='" . $row["forum"] . "') AND " .
      "(geek !='" . $_SESSION["userid"] . "')", $db);
    if (!$event_att_result) {
      $org_event['attendess'] = 0;
      error_log(mysql_error($db));
    }
    $org_event['attendees'] = mysql_num_rows($event_att_result);
    $org_event['messages'] = $row['messages'];
    array_push($template_details['org_events'], $org_event);
  }
} else {
  error_log(mysql_error($db));
}

$template_details['att_events'] = Array();
$result = mysql_query("SELECT name, firstname ,surname, messages,Events.id as evt, owner,type,day,hour,forum_duration FROM Events, people2, eventreg WHERE (Events.id = eventreg.event) AND (people2.id = Events.owner) and ((eventreg.geek='" . $_SESSION["userid"] . "') AND (geek != owner))order by day,hour,forum_duration", $db);
if (!$result) {
  error_log(mysql_error($db));
} else {
  $q = mysql_num_rows($result);
  If (($q > 0) || ($_SESSION["userstatus"] > 2)) {
    $template_details['registered_for'] = $q;
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
      $att_event['id'] = $row['evt'];
      $att_event['name'] = $row['name'];
      $att_event['owner'] = $row['owner'];
      $att_event['owner_name'] = $row['firstname'] . " " . $row['surname'];
      $att_event['attendees'] = mysql_num_rows(mysql_query("SELECT geek FROM eventreg WHERE (event=" . $row["evt"] . ") AND  (geek != $owner )"));
      $att_event['messages'] = $row['messages'];

      $att_event['schedule_text'] = $sched;

      array_push($template_details['att_events'], $att_event);
    }
    echo "</table><br />";
  }
}

$sql = "SELECT discussions.id AS mid, firstname, surname, subject, posted FROM discussions, people2 WHERE (people2.id=writer)  AND (forum = 1) ORDER BY posted";
$result = mysql_query($sql, $db);
$template_details['message_count'] = mysql_num_rows($result);

$template_details['messages'] = Array();
while ($msg = mysql_fetch_array($result)) {
  array_push($template_details['messages'], $msg);
}

//$sql = "SELECT sum(people2.attending + people2.children) AS total,country.code FROM people2,country WHERE country.id = people2.country AND people2.attending > 0 AND people2.status > 1 GROUP BY country.name;";
//$result = mysql_query($sql, $db);
//$count = mysql_num_rows($result);
//$drawmap_body = "data.addRows($count);\n" .
//    "data.addColumn('string', 'Country');\n" .
//    "data.addColumn('number', 'Attendees');\n";
//if ($count) {
//  $counter = 0;
//  while ($msg = mysql_fetch_array($result)) {
//    $drawmap_body .= "data.setValue($counter, 0, '" . $msg["code"] . "');\n";
//    $drawmap_body .= "data.setValue($counter, 1, $year);\n";
//    $counter++;
//  }
//}
//
//
//echo "$drawmap_header";
//echo "$drawmap_body";
//echo "$drawmap_footer";
//echo "<div id='map_canvas' style='text-align: center;'></div>\n";
//echo "<div id='map_canvas2' style='text-align: center;'></div>\n";
//HtmlTail();

//error_log(print_r($template_details));
$twig = GetTwig();
echo $twig->render('welcome.html', $template_details);
