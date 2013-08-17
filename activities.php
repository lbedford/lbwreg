<?php

require("basefunc.inc.php");

CheckLoggedInOrRedirect();

$db = ConnectMysql();
header("Pragma no_cache");

$template_details = GetBasicTwigVars($db);

$option = GetEntryFromRequest('option', "View");

$type_mapping = Array(
  1 => "workshops",
  2 => "lectures",
  3 => "hikes",
  4 => "community_events"
);

switch ($option) {
  case "Update":
    $db = ConnectMysql();
    $fid = GetEntryFromRequest('fid', -1);
    $evday = GetEntryFromRequest('event_day', '-1');
    $evhour = GetEntryFromRequest('event_hour', 0);
    $sql = "UPDATE Events SET day='$evday', hour='$evhour' WHERE id='$fid'";
    $result = mysql_query($sql, $db);
    if (!$result) {
      error_log(mysql_error($db));
    }
    header("Location: activities.php");
    exit();

  case "View":
    $template_details['workshops'] = array();
    $template_details['lectures'] = array();
    $template_details['hikes'] = array();
    $template_details['community_events'] = array();
    $query = "SELECT name, schedtxt, owner, messages, surname," .
        "firstname, day, hour, forum_duration, type, Events.id as fid " .
        "FROM people2, Events WHERE (people2.id=owner) " .
        "order by day,hour,fid";
    $result = (mysql_query($query, $db));
    if (!$result) {
      error_log("$query failed: " . mysql_error($db));
    } else {
      while ($myrow = mysql_fetch_array($result)) {
        $event = array();
        $type = $myrow['type'];
        $forum_id = $event['id'] = $myrow['fid'];
        $owner_id = $event['owner_id'] = $myrow['owner'];
        $query = "SELECT event FROM eventreg WHERE (event='$forum_id')" .
            " AND (geek != '$owner_id')";
        $event['subs'] = mysql_num_rows(mysql_query($query, $db));

        $event['day'] = $myrow['day'];
        $event['hour'] = $myrow['hour'];

        $event['schedule_text'] = Event2sched($myrow["type"], $myrow["day"],
          $myrow["hour"], $myrow["forum_duration"]);

        $event['name'] = $myrow['name'];
        $event['owner_name'] = GetLbwUserName($owner_id, $db);
        $event['messages'] = $myrow['messages'];
        $event['duration'] = $myrow['forum_duration'];
        array_push($template_details[$type_mapping[$type]], $event);
      }
    }
}
global $date;
$template_details['dates'] = $date;
$twig = GetTwig();
echo $twig->render('activities.twig', $template_details);