<?php
require("basefunc.inc.php");

function sendEmailToList($event)
{
  global $listmail, $frommail, $year;

  $ownername = getForumOwnerName($event);
  $event_name = getNameOfEvent($event);
  $event_description = getDescriptionOfEvent($event);
  mail($listmail, "New event proposed for LBW $year",
      "New event:\n\n" .
      "  Name: $event_name\n" .
      "\n" .
      "  El Proposador/in: $ownername\n" .
      "\n" .
      "  Description: $event_description" .
      "\n" .
      "Regards,\n\n" .
      "  The LBW $year registration site", "From: $frommail");
}

CheckLoggedInOrRedirect();

$heading = GetEntryFromRequest('heading', '');
$schedtxt = GetEntryFromRequest('schedtxt', '');
$type = GetEntryFromRequest('type', 0);
$description = GetEntryFromRequest('description', '');
$forum_duration = GetEntryFromRequest('forum_duration', 0);
$forum = GetEntryFromRequest('forum', 0);

$db = ConnectMysql();
if ($forum != 0) {
  $sql = "UPDATE Events SET schedtxt='$schedtxt', " .
      "name='$heading', description='$description', " .
      "forum_duration='$forum_duration' where id='$forum'";
  $result = mysql_query($sql, $db);
  if (!$result) {
    error_log("Failed to run $sql: " . mysql_error($db));
  }
} else {
  $now = time();
  $sql = sprintf("INSERT INTO Events (type, owner, name, " .
    "schedtxt, description, forum_duration, created)" .
    " VALUES (%d, %d, '%s', '%s', '%s', %d, %d)",
    $type, $_SESSION["userid"], $heading, $schedtxt, $description,
    $forum_duration, $now);
  $event = 0;
  if ($result = mysql_query($sql, $db)) {
    $event = mysql_insert_id($db);
    $result = mysql_query("INSERT INTO eventreg (geek,event) VALUES ('" . $_SESSION["userid"] . "','$event')", $db);
    if (!$result) {
      error_log("Insert into eventreg failed: " . mysql_error($db));
      header("Location: activities.php");
      exit();
    }
    header("Location: forum.php?forum=$event");
    sendEmailToList($event);
  } else {
    error_log("Insert into events failed: " . mysql_error($db));
  }
}
header("Location: activities.php");
