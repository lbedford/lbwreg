<?php
require("basefunc.inc.php");


CheckLoggedInOrRedirect();

$db = ConnectMysql();

$userforum = $_SESSION['userforum'];
$userid = $_SESSION['userid'];
$userstatus = $_SESSION['userstatus'];
$username = $_SESSION['username'];
$result = mysql_query("SELECT name, lastmessage, firstmessage, messages " .
"FROM Events WHERE id='$userforum'", $db);
if (!$result) {
  error_log('Failed to lookup forum: ' . mysql_error($db));
}
$foruminfo = mysql_fetch_array($result);

$submit = GetEntryFromRequest('submit', 'CANCEL');
if (isset($submit)) {
  switch ($submit) {
    case "write":
      $subject = GetEntryFromRequest('subject', 'General');
      $previous = GetEntryFromRequest('previous', -1);
      $message_body = '';
      # For the moment "comment" this out so it doesn't run.
      $previous = -1;
      if ($previous != -1) {
        $message_body_query = "SELECT message from discussions " .
            "where id = '$previous'";
        $message_body_result = mysql_query($message_body_query, $db);
        if (!$message_body_result) {
          error_log('Failed to read message: ' . mysql_error($db));
        } else {
          $message_body = mysql_fetch_array($message_body_result)['message'];
        }
        $message_body_lines = explode('\n', $message_body);
        foreach ($message_body_lines as &$line) {
          if (!substr_compare($line, "<br \>", strlen($line) - 6, 6)) {
            $line = "> $line<br \>";
          } else {
            $line = "> $line <br \>";
          }
        }
        $message_body = implode('\n', $message_body_lines);
      }
      HtmlHead("messages", "Message board", $userstatus, $userid);
      echo "<FORM METHOD=POST>";
      echo "Posting in Forum $userforum: " .
          $foruminfo["name"] . "<br><hr><br>\n";
      echo "From: $username<br>\n";
      echo "<INPUT TYPE=HIDDEN NAME=FORUM VALUE=$userforum>\n";
      echo "<INPUT TYPE=TEXT SIZE=50 NAME=subject VALUE = " .
          $subject . "><br>\n";
      echo "<TEXTAREA NAME=body COLS=60 ROWS=20>\n";
      echo $message_body;
      echo "</textarea>\n";
      echo "<br>\n";
      echo "<INPUT TYPE=SUBMIT NAME=submit VALUE=SAVE>\n";
      echo "<INPUT TYPE=SUBMIT NAME=submit VALUE=CANCEL>\n";
      echo "</form></center></td></tr>\n";
      HtmlTail();
      exit ();

    case "CANCEL":
      header("Location: forum.php?forum=$userforum");
      exit ();

    case "SAVE":
      $body = GetEntryFromRequest('body', '');
      $body = str_replace("\n\n", "<p>", $body);
      $body = str_replace("\n", "<br>", $body);
      $body = mysql_real_escape_string($body);
      $subject = GetEntryFromRequest('subject', '');
      $subject = mysql_real_escape_string($subject);
      $last = $foruminfo["lastmessage"];
      if (!$last) {
        $last = "0";
      }
      $sql =
          "INSERT INTO discussions (forum, writer, subject, message, " .
          "previous, next, posted) VALUES ($userforum, $userid" .
          ", '$subject', '$body', $last, 0, NOW() )";
      $result = mysql_query($sql, $db);
      if (!$result) {
        HtmlHead("messages", "Mysql error", $userstatus, $userid);
        printf("<br>%s<br>\n", $sql);
        printf("%s<br>\n", mysql_error());
        HtmlTail();
        exit ();
      }
      $thismessage = mysql_insert_id($db);

      $first = $foruminfo["firstmessage"];
      if (!$first) {
        $first = $thismessage;
      }
      $count = $foruminfo["messages"] + 1;
      $sql = "UPDATE Events SET messages=$count, lastmessage=$thismessage, " .
          "firstmessage=$first WHERE id=$userforum";
      if (!mysql_query($sql, $db))
        printf("(%s) %s<br/>", $sql, mysql_error($db));
      $sql = "UPDATE discussions SET next = '$thismessage' WHERE id = '$last'";
      if (!@mysql_query($sql, $db))
        printf("(%s) %s<br>", $sql, mysql_error($db));

      header("Location: messages.php?submit=browse");
      exit ();

    case "read":
      HtmlHead("messages", "Reading messages", $userstatus, $userid);
      $number = GetEntryFromRequest('number', -1);
      $result =
          mysql_query("SELECT writer, message, subject, posted, next, previous FROM discussions WHERE id = '$number'", $db);
      $row = mysql_fetch_array($result);
      $writer = $row['writer'];
      $posted = $row['posted'];
      $next = $row['next'];
      $previous = $row['previous'];
      $subject = $row['subject'];
      $message = $row['message'];
      $w = GetlbwUser($writer, $db);
      echo "<b>From:</b> " . $w[0] . " " . $w[1] . "<br>\n";
      echo "<b>Subject:</b> $subject<br>\n";
      echo "<b>Posted:</b> $posted<br>\n";
      if ($userid == $writer) {
        echo "<a href=messages.php?submit=delete&number=$number>[DELETE]</a>";
      }
      echo "<hr>\n";
      echo $message . "\n";
      $re = urlencode($subject);
      echo "<br><hr>\n";
      if ($userstatus > 2) {
        echo "<a href=messages.php?submit=write&subject=$re&previous=$number>[Reply]</a>&nbsp;";
      }
      $ref =
          ($userforum > 1) ? "forum.php#messages" : "welcome.php#messages";
      echo "<a href='" . $ref . "'>[Browse]</a>&nbsp;";
      if ($next > 0)
        echo "<a href=messages.php?submit=read&number=$next>[Next]</a>&nbsp;";
      if ($previous > 0)
        echo "<a href=messages.php?submit=read&number=$previous>[Previous]</a>";
      echo "\n<br>\n</body>\n</html>\n";
      exit ();
    case "delete":
      $firstmessage = $foruminfo['firstmessage'];
      $lastmessage = $foruminfo['lastmessage'];
      $number = GetEntryFromRequest('number', -1);
      $discussion_result = mysql_query(
        "SELECT previous, next FROM discussions WHERE id='$number'", $db);
      $msg_info = mysql_fetch_array($discussion_result);
      $forum = $msg_info['forum'];
      $previous = $msg_info['previous'];
      $next = $msg_info['next'];
      $event_result = mysql_query("SELECT messages FROM Events WHERE id='$forum'", $db);
      $fi = mysql_fetch_array($event_result);
      $messages = $fi['messages'] - 1;
      if ($firstmessage == $number)
        $firstmessage = $next;
      if ($lastmessage == $number)
        $lastmessage = $previous;
      if ($previous > 0)
        if (!mysql_query(
          "UPDATE discussions SET next = '$next' WHERE id='$previous'",
          $db)
        ) {
          printf("%s<br>", mysql_error($db));
        }
      if ($next > 0)
        if (!mysql_query(
          "UPDATE discussions SET previous = '$previous' WHERE id='$next'",
          $db)
        ) {
          printf("%s<br>", mysql_error($db));
        }
      if (!mysql_query("UPDATE Events SET firstmessage = '$firstmessage'" .
      ", lastmessage = '$lastmessage',messages='$messages'" .
      " WHERE id=$forum", $db)
      ) {
        printf("Error updating message list: %s<br>", mysql_error($db));
      }
      $number = GetEntryFromRequest('number', -1);
      mysql_query("DELETE FROM discussions WHERE id = '$number'", $db);
      header("Location: messages.php?submit=browse");
      break;

    case "browse":
      HtmlHead("messages", "Browsing Forum $userforum", $userstatus, $userid);

      $q = "SELECT name FROM Events WHERE id = '$userforum'";
      $result = mysql_query($q, $db);
      if (!$result) {
        echo mysql_error($db) . "<br />\n";
      }
      $s = mysql_fetch_array($result);
      echo "<h2>Messages in Forum $userforum: " . stripslashes($s["name"]) .
          "</h2>\n";
      echo "<hr>\n";
      $sql =
          "SELECT id, writer, subject, posted FROM discussions " .
          "WHERE forum = $userforum ORDER BY posted";
      $result = mysql_query($sql, $db);
      if (!$result) {
        echo mysql_error($db) . "<br \>/n";
      }
      echo "<table class='reginfo'>\n<tr>\n";
      echo "<th>From</th>\n";
      echo "<th>Subject</th>\n";
      echo "<th>Time</th>\n";
      echo "<th>&nbsp;</th>\n";
      echo "</tr>\n";
      while ($msg_info = mysql_fetch_array($result)) {
        $w = GetlbwUser($msg_info["writer"], $db);
        echo "<tr>\n";
        echo "<td>" . $w[0] . " " . $w[1] . "</td>\n";
        echo "<td>" . stripslashes($msg_info["subject"]) . "</td>\n";
        echo "<td>" . $msg_info["posted"] . "</td>\n";
        echo "<td><a href=messages.php?submit=read&number=" .
            $msg_info["id"] . ">Read</a></td>\n";
        echo "</tr>\n";
      }
      echo "</table>\n";
      $target = $userforum ? "forum" : "welcome";
      echo "<br>\n";
      echo "<a href=messages.php?submit=write>[Post a message]</a>";
      echo "&nbsp;<a href='" . $target . ".php'>[Back to Forum]</a>";
      HtmlTail();
      exit ();

    case "browseall":
      HtmlHead("messages", "Browsing Forums", $userstatus, $userid);
      echo "<h2>Message Forums on LBW</h2><hr>";
      printf("<table class='reginfo'>");

      $sql = "SELECT id, subject FROM discussions WHERE forum = 1";
      $open_forum_result = mysql_query($sql, $db);
      if (!$open_forum_result) {
        echo mysql_error($db) . "<br/>\n";
      }
      echo "<tr>\n";
      echo "<td>Open Forum</td>\n";
      echo "<td>" . mysql_num_rows($open_forum_result) . "</td></tr>";

      $sql = "SELECT id, name, type  FROM Events ORDER BY type, id";
      $result = mysql_query($sql, $db);
      if (!$result) {
        echo mysql_error($db) . "<br/>\n";
      }

      while ($labels = mysql_fetch_array($result)) {
        $sql =
            "SELECT id,subject FROM discussions WHERE forum = " . $labels["id"];

        $forum_result = mysql_query($sql, $db);
        if (!$forum_result) {
          echo "</table>" . mysql_error($db) . "<br/>\n";
        }
        $count = mysql_num_rows($forum_result);
        echo "<tr>\n";
        echo "<td>" . stripslashes($labels["name"]) . "</td>\n";
        echo "<td>$count</td>\n";
        echo "</tr>\n";
      }
      printf("</table>");
      HtmlTail();
      exit ();
  }
}
HtmlHead("messages", "Snafu", $userstatus, $userid);
echo "<h1> A Foul-up Has Occurred!</h1><hr>\n";
if ($_SESSION["userid"]) {
  $target = $userforum ? "forum" : "welcome";
} else {
  $target = "login";
  echo "Sorry but you will have to login again<br>";
}
echo "<a href='" . $target . "'>CONTINUE</a>";
HtmlTail();