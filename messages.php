<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();

$db = ConnectMysql();

$userid = $_SESSION['userid'];
$userstatus = $_SESSION['userstatus'];
$username = $_SESSION['username'];
$forum_id = GetEntryFromRequest('forum', 1);
$result = mysql_query("SELECT name, lastmessage, firstmessage " .
"FROM Events WHERE id='$forum_id'", $db);
if (!$result) {
  error_log('Failed to lookup forum: ' . mysql_error($db));
}
$foruminfo = mysql_fetch_array($result);
$template_details = GetBasicTwigVars($db);

$submit = GetEntryFromRequest('submit', 'browseall');
switch ($submit) {
  case "SAVE":
    $body = GetEntryFromRequest('body', '');
    $body = str_replace("\n\n", "<p>", $body);
    $body = str_replace("\n", "<br>", $body);
    $body = mysql_real_escape_string($body);
    $subject = GetEntryFromRequest('subject', '');
    $last = $foruminfo["lastmessage"];
    if (!$last) {
      $last = "0";
    }
    $sql =
        "INSERT INTO discussions (forum, writer, subject, message, " .
        "previous, next, posted) VALUES ($forum_id, $userid" .
        ", '$subject', '$body', $last, 0, NOW() )";
    $result = mysql_query($sql, $db);
    if (!$result) {
      error_log("Error running $sql: " . mysql_error($db));
    } else {
      $thismessage = mysql_insert_id($db);

      $first = $foruminfo["firstmessage"];
      if (!$first) {
        $first = $thismessage;
      }
      $sql = "UPDATE Events SET lastmessage=$thismessage, " .
          "firstmessage=$first WHERE id=$forum_id";
      if (!mysql_query($sql, $db)) {
        error_log("Error running $sql: " . mysql_error($db));
      }
      $sql = "UPDATE discussions SET next = '$thismessage' WHERE id = '$last'";
      if (!@mysql_query($sql, $db)) {
        error_log("Error running $sql: " . mysql_error($db));
      }
    }

    header("Location: forum.php?forum=$forum_id");
    exit ();

  case "read":
    $number = GetEntryFromRequest('number', -1);
    $result =
        mysql_query("SELECT writer, message, subject, posted, next, previous FROM discussions WHERE id = '$number'", $db);
    $row = mysql_fetch_array($result);
    $template_details['writer'] = $row['writer'];
    $template_details['posted'] = $row['posted'];
    $template_details['next'] = $row['next'];
    $template_details['previous'] = $row['previous'];
    $template_details['message_subject'] = $row['subject'];
    $template_details['body'] = $row['message'];
    $template_details['writer_name'] = GetLbwUserName($row['writer'], $db);
    $template_details['owner'] = ($userid == $row['writer']);
    $template_details['forum_id'] = $forum_id;
    $template_details['message_id'] = $number;
    $template_details['event_name'] = $foruminfo['name'];
    break;

  case "delete":
    $firstmessage = $foruminfo['firstmessage'];
    $lastmessage = $foruminfo['lastmessage'];
    $number = GetEntryFromRequest('number', -1);
    $discussion_result = mysql_query(
      "SELECT previous, next FROM discussions WHERE id='$number'", $db);
    $msg_info = mysql_fetch_array($discussion_result);
    $previous = $msg_info['previous'];
    $next = $msg_info['next'];
    if ($firstmessage == $number) {
      $firstmessage = $next;
    }
    if ($lastmessage == $number) {
      $lastmessage = $previous;
    }
    if ($previous > 0) {
      if (!mysql_query(
        "UPDATE discussions SET next = '$next' WHERE id='$previous'",
        $db)
      ) {
        error_log(mysql_error($db));
      }
    }
    if ($next > 0) {
      if (!mysql_query(
        "UPDATE discussions SET previous = '$previous' WHERE id='$next'",
        $db)
      ) {
        error_log(mysql_error($db));
      }
    }
    if (!mysql_query("UPDATE Events SET firstmessage = '$firstmessage'" .
    ", lastmessage = '$lastmessage' WHERE id='$forum_id'", $db)
    ) {
      error_log(mysql_error($db));
    }
    $number = GetEntryFromRequest('number', -1);
    mysql_query("DELETE FROM discussions WHERE id = '$number'", $db);
    header("Location: forum.php?forum=$forum_id#messages");
    break;
}
$twig = GetTwig();
echo $twig->render('message_read.twig', $template_details);
exit();