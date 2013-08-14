<?php
include("basefunc.inc.php");

CheckLoggedInOrRedirect();

$db = ConnectMysql();
header("Pragma: no_cache");

global $longdate, $date, $weekday;

$template_details = GetBasicTwigVars();

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
  $query = "SELECT COUNT(geek) FROM eventreg WHERE event='$event_id'";
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

$schedule = Array();

for ($day_index = 1; $day_index < count($date) - 2; $day_index++) {
  $sched['short'] = array();
  $sched['medium'] = array();
  $sched['long'] = array();

  $default_details = Array('id' => 0, 'rowspan' => 1, 'display' => true,
    'conflicts' => 0);
  for ($hour = 0; $hour < 24; $hour++) {
    $sched['short'][$hour] = $default_details;
    $sched['medium'][$hour] = $default_details;
    $sched['long'][$hour] = $default_details;
  }

  $event_sql = "SELECT id,schedtxt,hour,forum_duration " .
      "FROM Events where (day='$day_index') and type != 1 " .
      "ORDER BY hour,forum_duration";
  $result = mysql_query($event_sql, $db);
  $conflict_name = "<span class='conflict'>*** Conflict ***</span>";

  while ($row = mysql_fetch_array($result)) {
    $h = $row["hour"];
    $event_details = Array();
    $dur = $event_details['rowspan'] = $row["forum_duration"];
    $event_details['attendees'] = CalculateRegistered($db, $row["id"]);
    $event_details['potential'] = CalculatePotentialAttendees($db, $row["id"]);
    $event_details['missing_attendees'] = (
        $event_details['attendees'] == $event_details['potential']);
    $event_details['id'] = $row['id'];
    $event_details['name'] = $row['schedtxt'];

    if ($dur < 3) {
      $slottype = 'short';
    } // One hour slot(s)
    else if ($dur < 5) {
      $slottype = 'medium';
    } // Half day slot
    else {
      $slottype = 'long';
    } // Whole day slot

    $conflict = false;
    if ($sched[$slottype][$h] != $default_details) {
      $conflict = true;
    }
    $sched[$slottype][$h] = array_merge($sched[$slottype][$h], $event_details);

    if ($conflict) {
      $sched[$slottype][$h]['name'] = $conflict_name;
    }

    if ($dur > 1) {
      for ($offset = 1; $offset < $dur; $offset++) {
        $sched[$slottype][$h + $offset] = array_merge(
          $sched[$slottype][$h + $offset], $event_details);
        $sched[$slottype][$h + $offset]['display'] = false;
      }
    }
  };

  $sched['name'] = $longdate[$day_index];
  $sched['arrivals'] = CalculateArrivals($db, $day_index);
  $sched['departures'] = CalculateDepartures($db, $day_index);
  $sched['attendees'] = CalculateAttendees($db, $day_index);

  for ($h = 0; $h < 24; $h++) {
    $e1 = $sched['short'][$h]['id'];
    $e2 = $sched['short'][$h]['id'];
    $sched['short'][$h]['conflicts'] = CalculateConflicts($db, $e1, $e2);
    $e1 = $sched['medium'][$h]['id'];
    $e2 = $sched['long'][$h]['id'];
    $sched['medium'][$h]['conflicts'] = CalculateConflicts($db, $e1, $e2);
  }
  array_push($schedule, $sched);
}

$template_details['schedule'] = $schedule;
//print_r($template_details['schedule'][0]['short']);
$twig = GetTwig();
echo $twig->render('schedule.twig', $template_details);