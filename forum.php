<?php
require("basefunc.inc.php");

function sendMailToEventOwner($event, $new_user, $db)
{
  $ownermail = getForumOwnerEmail($event);
  $event_name = getNameOfEvent($event);
  $new_user_name = GetLbwUserName($new_user, $db);
  $registrations = getListOfUsersForEvent($event);
  global $year;
  mail($ownermail, "New registration for your event: $event_name",
      "New registration:\n\n" .
      "  Name: $new_user_name\n" .
      "\n" .
      "Total Attendees:\n" .
      "$registrations\n" .
      "Regards,\n\n" .
      "  The LBW $year registration site", "");
}

function CanUserAttend($user, $forum, $db)
{
  $query = "SELECT count(people2.id) from people2, Events where " .
      "people2.arrival < Events.day AND " .
      "people2.departure > Events.day and Events.id='$forum' " .
      "and people2.id='$user'";
  $attend_result = mysql_query($query, $db);
  if (!$attend_result) {
    error_log("$query failed: " . mysql_error($db));
  } else {
    return mysql_fetch_array($attend_result)[0];
  }
  return 0;
}

CheckLoggedInOrRedirect();

$userid = $_SESSION['userid'];
$userstatus = $_SESSION['userstatus'];
$db = ConnectMysql();
$template_details = GetBasicTwigVars($db);

$forum = intval(GetEntryFromRequest('forum', -1));

if ($forum < 2) {
  header("Location: welcome.php"); // if forum = 1 then send user to welcome page
}

$template_details['forum_id'] = $forum;
$submit = GetEntryFromRequest('submit', '');

// check for register / de-register
switch ($submit) {
  case "REGISTER":
    $sql = "INSERT INTO eventreg (event, geek) VALUES('$forum','$userid')";
    $result = mysql_query($sql, $db);
    if (!$result) {
      error_log(mysql_error($db));
      $template_details['has_errors'] = 1;
      $template_details['error'] = "Problem registering for this event, please contact organisers";
    } else {
      sendMailToEventOwner($forum, $userid, $db);
    }
    break;
  case "UNREGISTER":
    $sql = "DELETE FROM eventreg WHERE (geek='$userid' AND event='$forum')";
    $result = mysql_query($sql, $db);
    if (!$result) {
      error_log(mysql_error($db));
      $template_details['has_errors'] = 1;
      $template_details['error'] = "Problem unregistering from this event, please contact organisers";
    }
    break;
}

$query = "SELECT owner, type, name, schedtxt, description, forum_duration " .
    "FROM Events WHERE id='$forum'";
$result = mysql_query($query, $db);
if (!$result) {
  error_log(mysql_error($db));
  $template_details['has_errors'] = 1;
  $template_details['error'] = "Problem loading this event, please contact organisers";
}

$foruminfo = mysql_fetch_array($result);
$owner_id = $foruminfo['owner'];

$event_admin = ($owner_id == $userid);
$template_details['event_type'] = $foruminfo['type'];
$template_details['event_heading'] = $foruminfo['name'];
$template_details['event_description'] = $foruminfo['description'];
$template_details['event_duration'] = $foruminfo['forum_duration'];
$template_details['event_schedtxt'] = $foruminfo['schedtxt'];
// find out if user is registered for this event or not
$sql = "SELECT event FROM eventreg WHERE geek='$userid' AND event='$forum'";
$result = mysql_query($sql, $db);
$registered = false;
if (!$result) {
  error_log(mysql_error($db));
  $template_details['has_errors'] = 1;
  $template_details['error'] = "Problem loading this event, please contact organisers";
} else {
  $registered = (mysql_num_rows($result) > 0);
}

$template_details['event_name'] = $foruminfo['name'];
$template_details['owner_id'] = $foruminfo['owner'];
$template_details['owner_name'] = GetLbwUserName($owner_id, $db);
$template_details['description'] = $foruminfo['description'];
$template_details['admin'] = $event_admin || ($userstatus > 8);
$template_details['owner'] = $event_admin;
$template_details['owner_attend'] = 1;
if ($foruminfo['type'] != 1) {
  $template_details['owner_attend'] = CanUserAttend($foruminfo['owner'], $forum, $db);
}

$result = mysql_query("SELECT day FROM Events WHERE id='$forum'");
$day = -1;
if (!$result) {
  error_log("$query failed: " . mysql_error($db));
} else {
  $row = mysql_fetch_row($result);
  $day = $row[0];
  if (is_null($day)) {
    $day = -1;
  }
}
global $date;
$template_details["day"] = $date[$day];

$query = "SELECT id,firstname, surname FROM eventreg, people2 " .
    "WHERE (people2.id=eventreg.geek) AND (eventreg.event='$forum')" .
    "AND (people2.id != '$owner_id')";
$result = mysql_query($query, $db);
$count = 0;

if (!$result) {
  error_log(mysql_error($db));
} else {
  $count = mysql_num_rows($result);
}

$template_details["registered"] = $count;
if ($count) {
  $template_details["registered_users"] = Array();
  while ($reg = mysql_fetch_array($result)) {
    $user = $reg;
    $user['attend'] = 1;
    if ($foruminfo['type'] != 1) {
      $user['attend'] = CanUserAttend($reg['id'], $forum, $db);
    }
    array_push($template_details['registered_users'], $user);
  }
}

$template_details['num_messages'] = 0;

$sql = "SELECT discussions.id as mid, firstname, surname, subject, posted " .
    "FROM discussions, people2 WHERE (people2.id=writer)  AND " .
    "(forum = '$forum') ORDER BY posted";
$result = mysql_query($sql, $db);
$template_details['messages'] = Array();
if (!$result) {
  error_log("$sql failed: " . mysql_error($db));
} else {
  $template_details['num_messages'] = mysql_num_rows($result);

  while ($msg = mysql_fetch_array($result)) {
    array_push($template_details['messages'], $msg);
  }
}


$twig = GetTwig();
/** @noinspection PhpUndefinedMethodInspection */
echo $twig->render('forum.twig', $template_details);
exit();