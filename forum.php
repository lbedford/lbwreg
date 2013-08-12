<?php
require("basefunc.inc.php");

function sendMailToEventOwner($event, $new_user)
{
  $ownermail = getForumOwnerEmail($event);
  $event_name = getNameOfEvent($event);
  $new_user_name = getUsername($new_user);
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

$_SESSION["userid"] = 0;
session_start();
if (!$_SESSION["userid"]) {
  header("Location: login.php");
  exit();
}

/* variables from the environment (GET/POST) */
extract($_REQUEST, EXTR_SKIP);

$db = ConnectMysql();

if (isset($forum)) // forum is passed on the command line if a change of forum takes place
$_SESSION["userforum"] = $forum;
else
  $forum = $_SESSION["userforum"];

if ($forum < 2) {
  header("Location: welcome.php"); // if forum = 1 then send user to welcome page
}

$forum = mysql_real_escape_string(trim($forum));

if (isset($submit)) {
  // check for register / de-register
  if ($submit == "REGISTER") {
    $sql = "INSERT INTO eventreg (event, geek) VALUES($forum," . $_SESSION["userid"] . ")";
    $result = mysql_query($sql, $db);
    if (!$result) {
      printf("%s<br>", mysql_error($db));
    } else {
      sendMailToEventOwner($forum, $_SESSION["userid"]);
    }
  }
  if ($submit == "UNREGISTER") {
    $sql = "DELETE from eventreg where (geek=" . $_SESSION["userid"] . " AND event=$forum)";
    $result = mysql_query($sql, $db);
    if (!$result)
      printf("%s<br>", mysql_error($db));
  }
}


$query = "SELECT owner, type, name, description, messages FROM Events WHERE id='$forum'";
$result = mysql_query($query, $db);
$foruminfo = mysql_fetch_array($result);
$owner = $foruminfo["owner"];

$honcho = (($foruminfo["owner"]) == $_SESSION["userid"]);

// find out if user is registered for this event or not
$sql = "SELECT event FROM eventreg where geek=" . $_SESSION["userid"] . " AND event=$forum";
$result = mysql_query($sql, $db);
$registered = (mysql_num_rows($result) > 0) ? 1 : 0;

$honchostr = "Placeholder by";
switch ($foruminfo["type"]) {
  case 1: // Workshop;
    $honchostr = "A Workshop moderated by";
    break;

  case 2: // Seminar
    $honchostr = "A Class presented by";
    break;

  case 3: // Excursion or hike
    $honchostr = "An Excursion organised by";
    break;

  case 4: // Community Event
    $honchostr = "Community event led by";
    break;
}


HtmlHead("forum", $foruminfo["name"], $_SESSION["userstatus"], $_SESSION["userid"]);
echo "";

$nv = GetlbwUser($foruminfo["owner"], $db);
echo "<table class='reginfo'>";
printf("<tr ><TH COLSPAN=3> %s <br> %s <br><A href=userview.php?user=%d>%s %s</a></td></tr>\n",
  stripslashes($foruminfo["name"]), $honchostr, $foruminfo["owner"], $nv[0], $nv[1]);
printf("<tr><TD COLSPAN=3>%s</td></tr>\n", text2html(stripslashes($foruminfo["description"])));


if ($honcho || $_SESSION["userstatus"] >= 16) { // ------------- Show Forum Owners options --------------
  ?>
  <tr>
    <th>
      <a href=forumedit.php?forum=<?php echo $forum ?> >Edit the event information</a>
    </th>
    <th>&nbsp;</th>
    <th>
      <a href=forumedit.php?option=cancel&forum=<?php echo $forum ?>>Cancel the event</a>
    </th>
  </tr>
<?php
}
if (!$honcho) {
  if ($_SESSION["userstatus"] > 2) {
    echo "<tr><td>&nbsp;</td><td><FORM METHOD=POST><INPUT TYPE=HIDDEN NAME=forum VALUE=$forum>\n";
    printf("<INPUT TYPE=SUBMIT NAME=submit VALUE=%s></form></td><td>&nbsp;</td></tr>",
      $registered ? "UNREGISTER" : "REGISTER");
  } else {
    echo "<tr ><TD COLSPAN=3>";
    echo "You will be able to register to attend this event when your access has been approved by the organisers";
    echo "<br>We Regret the inconvenience caused by Script Kiddies<br></td></tr>";
  }
}

$result = mysql_query("SELECT day FROM Events WHERE id='$forum'");
if (!$result) {
  error_log("$query failed");
  $day = null;
} else {
  $row = mysql_fetch_row($result);
  $day = $row[0];
  global $date;
  echo "<TR><TH COLSPAN=3>This event is currently scheduled for " . $date[$day] . "</TH></TR>";
}


$query = "SELECT geek,event,firstname,surname FROM eventreg,people2 " .
    "WHERE (people2.id=geek) AND (event='$forum') AND (geek != '$owner')";
$result = mysql_query($query, $db);
if (!$result) {
  error_log(mysql_error($db));
}
$count = mysql_num_rows($result);
echo "<tr ><TH COLSPAN=3>There are $count people registered for this activity:</th></tr>";
if ($count) {
  echo "<tr><TD COLSPAN=3><table class='reginfo'>";
  $col = 0;
  while ($reg = mysql_fetch_array($result)) {
    if (($col % 3) == 0)
      echo "<tr>";
    $col++;
    echo "<td>";
    $attend = 0;
    $span_class = "";
    if ($day > 0) {
      $query = "SELECT count(people2.id) from people2, Events where " .
          "people2.arrival < Events.day AND " .
          "people2.departure > Events.day and Events.id = $forum " .
          "and people2.id=" . $reg["geek"];
      $attend_result = mysql_query($query, $db);
      if (!$attend_result) {
        error_log("$query failed");
      } else {
        $row = mysql_fetch_array($attend_result);
        $attend = $row[0];
      }
      if ($attend < 1) {
        $span_class = "class='missing_attendees'";
      }
    }
    echo "<span " . $span_class . ">";
    printf("<A href=userview.php?user=%d>%s %s</a>", $reg["geek"],
      $reg["firstname"], $reg["surname"]);
    echo "</span>";
    if (($col % 3) != 0) {
      echo ",";
    }
    echo "</td>";
    if (($col % 3) == 0)
      echo "</tr>\n";
  }
  if ($col % 3) {
    while ($col % 3) {
      echo "<td>&nbsp;</td>";
      $col++;
    }
    echo "</tr>";
  }
  echo "</table>"; // the inner one;
}

echo "</td></table>";


// Message board
echo "<A name=messages></a>";
echo "<table class='reginfo'>";
$post_message = "<A href=messages.php?submit=write>Post a message</a>";
printf("<tr ><TD COLSPAN=4>Message Board<br>%d Messages in this Forum",
  $foruminfo["messages"]);
printf("<br>%s</td></tr>\n", ($_SESSION["userstatus"] > 2) ? $post_message : "");
if ($foruminfo["messages"]) {
  $sql = "SELECT discussions.id as mid, firstname, surname, subject, posted " .
      "FROM discussions, people2 WHERE (people2.id=writer)  AND " .
      "(forum = " . $_SESSION["userforum"] . ") ORDER BY posted";
  $result = mysql_query($sql, $db);
  if (!$result)
    printf("%s<br>", mysql_error($db));
  echo "<tr><TH>From</th><TH>Subject</th><TH>Time</th><th>&nbsp;</th></tr>";
  while ($msg = mysql_fetch_array($result)) {
    printf("<tr><td>%s %s</td><td>%s</td><td>%s</td><td>" .
      "<A HREF=messages.php?submit=read&number=%d>Read</a></td></tr>\n",
      $msg["firstname"], $msg["surname"], $msg["subject"],
      $msg["posted"], $msg["mid"]);
  }
}
printf("</table>");

HtmlTail();
?>
