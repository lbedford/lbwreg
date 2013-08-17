<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();

$db = ConnectMysql();
if (!array_key_exists('option', $_REQUEST)) {
  $option = "Edit";
} else {
  $option = $_REQUEST['option'];
}


function honchocheck($user, $evt, $db)
{
  $result = mysql_query("SELECT owner FROM Events WHERE id='" . $evt . "'", $db);
  if ($row = mysql_fetch_array($result)) {
    if ($user == $row["owner"] || $_SESSION["userstatus"] >= 16)
      return 1;
  }
  return 0;
}

switch ($option) {
  case "Edit":
    $query = "SELECT owner, name, schedtxt, description, type, number," .
        "forum_duration FROM Events WHERE id=$forum";
    $result = mysql_query($query, $db);
    $myrow = mysql_fetch_array($result);
    if ($_SESSION["userid"] == $myrow["owner"] || $_SESSION["userstatus"] >= 16) {

      HtmlHead("forumedit", "Forum Edit", $_SESSION["userstatus"], $_SESSION["userid"]);
      printf("<FORM METHOD=POST>\n");
      printf("<INPUT TYPE=HIDDEN NAME=event VALUE=%d>\n", $forum);
      printf("Title<br>\n");
      printf("<INPUT TYPE=TEXT NAME=\"heading\" VALUE=\"%s\"  SIZE=50><br>",
        stripslashes($myrow["name"]));
      printf("Schedule entry<br>\n");
      printf("<INPUT TYPE=TEXT NAME=\"schedtxt\" VALUE=\"%s\"  SIZE=12><br>",
        stripslashes($myrow["schedtxt"]));
      printf("Details <br>\n");
      printf("<TEXTAREA NAME=description COLS=70 ROWS=20>\n%s", $myrow["description"]);
      echo "</textarea><br>\n";
      switch ($myrow["type"]) {
        case 1:
          echo "<INPUT TYPE=HIDDEN NAME=number VALUE=7>\n";
          echo "<INPUT TYPE=HIDDEN NAME=forum_duration VALUE=24>\n";
          break;
        case 2:
          echo "I would like to have <SELECT name=\"sessions\">";
          for ($i = 1; $i <= 4; $i++) {
            $s = ($i == $myrow["number"]) ? "selected" : "";
            printf("<option value='$i' %s> $i\n", $s);
          }
          echo "\n</select> Sessions of";
          echo "<select name=\"forum_duration\" >";
          for ($i = 1; $i <= $eventmaxhours[$myrow["type"]]; $i++) {
            $s = ($i == $myrow["forum_duration"]) ? "selected" : "";
            echo "<option value='$i' " . $s . "> $i\n";
          }
          echo "\n</select> Hour forum_duration.<br>\n";
          break;
        case 3:
        case 4:
          echo "<input type='hidden' name='number' value='1'>\n";
          printf("The Event will be about <select name='forum_duration' >");
          for ($i = 1; $i <= $eventmaxhours[$myrow["type"]]; $i++) {
            $s = ($i == $myrow["forum_duration"]) ? "selected" : "";
            echo "<option value='$i' " . $s . "> $i\n";
          }
          printf("</select> hours long<br>\n");
          break;
      }

      global $date;
      if ($myrow["type"] != 1) {
        $preferred_days = 0;

        for ($day = 0; $day <= $duration + 1; $day++) {
          $checked = $preferred_days & 1 ? "checked" : "";
          $preferred_days = $preferred_days >> 1;
          echo "<input type='checkbox' name='preferred_day$day' $checked>" . $date[$day] . "</input><br>\n";
        }
      }
      echo "<input type='submit' name='option' value='Save'>&nbsp;" .
          "<input type='submit' name='option' value='Abort'>\n";
      echo "</form><br><hr>";
      HtmlTail();
      exit();
    }
    break;

  case "Save":
    //comes from Edit option:
    if (!honchocheck($_SESSION["userid"], $event, $db))
      break;
    $number = GetEntryFromRequest('number', -1);
    $heading = addslashes(GetEntryFromRequest('heading', ''));
    $description = addslashes(GetEntryFromRequest('description', ''));
    $schedtxt = GetEntryFromRequest('schedtxt', '');
    $forum_duration = GetEntryFromRequest('forum_duration', 1);
    $sql = "UPDATE Events SET name='$heading', schedtxt='$schedtxt', " .
        "description='$description', number='$number', forum_duration='$forum_duration' WHERE id=$event";
    $result = mysql_query($sql, $db);
    break;

  case "cancel"; // comes from honcho options in forum.
    if (!honchocheck($_SESSION["userid"], $forum, $db)) {
      break;
    }
    HtmlHead("forumedit", "Forum Edit", $_SESSION["userstatus"], $_SESSION["userid"]);
    echo "<h1>Are You Sure you want to Cancel this Event?</h1>";
    echo "<FORM METHOD = POST>" .
        "<INPUT TYPE=HIDDEN NAME=\"event\" VALUE=\"$forum\">" .
        "<INPUT TYPE=SUBMIT NAME=option VALUE=\"CONFIRM DELETE EVENT\">" .
        "<INPUT TYPE=SUBMIT NAME=option VALUE=\"OOPS! WRONG BUTTON\">" .
        "<br>\n";
    HtmlTail();
    exit();

  case "CONFIRM DELETE EVENT"; //comes from Cancel
    if (!honchocheck($_SESSION["userid"], $event, $db)) {
      break;
    }
    $result = mysql_query("DELETE FROM eventreg WHERE event='$forum'", $db);
    $result = mysql_query("DELETE FROM Events WHERE id='$forum'", $db);
    if ($_SESSION["userforum"] == $forum)
      $_SESSION["userforum"] = 1;
    header("Location: activities.php");
    exit();

  case "OOPS! WRONG BUTTON":
    break;
  default:
    break;
}

header("Location: forum.php");