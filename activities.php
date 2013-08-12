<?php

require("basefunc.inc.php");

/* variables from the environment (GET/POST) */
/*extract($_REQUEST, EXTR_SKIP);*/

session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

$db = ConnectMysql();
header("Pragma no_cache");

$honchostr[1] = "Co-ordinator";
$honchostr[2] = "Speaker";
$honchostr[3] = "Organiser";
$honchostr[4] = "Organiser";
$forumstr[1] = "Workshops";
$forumstr[2] = "Lectures &amp; Seminars";
$forumstr[3] = "Excursions &amp; Hikes";
$forumstr[4] = "Community Events";
$namestr[1] = "Workshop Project";
$namestr[2] = "Subject";
$namestr[3] = "Destination";
$namestr[4] = "Activity";
$propstr[1] = "manage a workshop project";
$propstr[2] = "present a class";
$propstr[3] = "organise a hike or excursion";
$propstr[4] = "organise a community event";

if (!isset($option))
  $option = "View";

switch ($option) {
  case "Update":
    $db = ConnectMysql();
    $now = time();
    $rows = $_REQUEST['rows'];
    for ($r = 0; $r < $rows; $r++) {
      $fid = $_REQUEST["fid$r"];
      $evday = $_REQUEST["event${fid}day"];
      $evhour = $_REQUEST["event${fid}hour"];
      $sql = "UPDATE Events SET day='$evday', hour='$evhour'," .
          "type='$type', tslot='1' WHERE id='$fid'";
      $result = mysql_query($sql, $db);
      if (!$result)
        echo mysql_error($db);
    }
    header("Location: activities.php");
    exit();

  case "View":

    HtmlHead("activities", "LBW $year Activities", $_SESSION["userstatus"], $_SESSION["userid"]);

    echo "";

    for ($type = 1; $type <= 4; $type++) {
      echo "<table class='events'>\n";
      echo "<tr >";
      echo "<td colspan='5'><b>$forumstr[$type]</b></td>";
      echo "</tr>\n";
      echo "<tr ><th class='event_name'>$namestr[$type]</th>\n";
      echo "<th class='event_organiser'>$honchostr[$type]</th>\n";
      echo "<th class='event_subs'>Subs.</th>\n";
      echo "<th class='event_messages'>Msg.</th>\n";
      echo "<th class='event_schedule'>Schedule</th></tr>\n";
      if (($_SESSION["userstatus"] == 16) && ($type > 1)) {
        echo "<form method=post>";
      }
      $query = "SELECT name, schedtxt, owner, messages, surname," .
          "firstname, day, hour, forum_duration, type, Events.id as fid " .
          "FROM people2, Events WHERE (people2.id=owner) AND " .
          "(type=$type) order by day,hour,fid";
      $result = (mysql_query($query, $db));
      $row = 0;
      while ($myrow = mysql_fetch_array($result)) {
        $query = "SELECT event FROM eventreg WHERE (event="
            . $myrow["fid"] . ") AND (geek !=" . $myrow["owner"] . ")";
        $punters = mysql_num_rows(mysql_query($query, $db));
        $bgc = "";
        $bgc1 = "";
        global $date, $shortday;
        if ($_SESSION["userstatus"] == 16) {
          if ($type == 1) {
            $sched = "All Week";
          } else {
            $sched = "<input type=hidden name=fid$row value=" . $myrow["fid"] . ">" . $myrow["forum_duration"] . " hr: <select name=event" . $myrow["fid"] . "day>";
            $row++;
            if ($myrow["day"] == 0)
              $bgc = " class='unscheduled'";
            elseif (!$myrow["schedtxt"])
              $bgc1 = " class='unscheduled'";
            for ($evday = 0; $evday < count($date) - 2; $evday++) {
              if ($myrow["day"] == $evday)
                $sel = "selected";
              else
                $sel = "";
              $sched .= "<option value=$evday $sel>$shortday[$evday] " . $date[$evday] . "</option>\n";
            }
            $sel = "";
            if (is_null($myrow["day"])) {
              $sel = "selected";
            }
            $sched .= "<option value=null " . $sel . ">Unset</option>\n";
            $sched .= "</select>\n";
            $sched .= "<select name=event" . $myrow["fid"] . "hour>";
            for ($evhour = 0; $evhour < 24; $evhour++) {
              if ($myrow["hour"] == $evhour)
                $sel = " selected";
              else
                $sel = "";
              $sched .= "<option value=$evhour$sel>$evhour:00";
            }
            $sched .= "</select>";
          }
        } else {
          $sched = Event2sched($myrow["type"], $myrow["day"],
            $myrow["hour"], $myrow["forum_duration"]);
        }

        echo "<tr >";
        echo "<td class='event_name' " . $bgc . $bgc1 . ".>";
        echo "<a href=forum.php?forum=" . $myrow["fid"] . ">" .
            stripslashes($myrow["name"]) . "</a>";
        echo "</td>";
        echo "<td class='event_owner' " . $bgc . ">";
        echo "<a href='userview.php?user=" . $myrow["owner"] . "'>" .
            $myrow["firstname"] . " " . $myrow["surname"] . "</a>";
        echo "</td>";
        echo "<td align='center' class='event_subs' " . $bgc . ">" . $punters . "</td>";
        echo "<td align='center' class='event_messages' " . $bgc . ">" . $myrow["messages"] . "</td>";
        echo "<td class='event_schedule' " . $bgc . ">" . $sched . "</td></tr>\n";
      }
      if (($_SESSION["userstatus"] > 2) && ($type < 5)) {
        if (($_SESSION["userstatus"] == 16) && ($type > 1))
          echo "<tr ><td colspan=4>&nbsp;</td><td><input type=hidden name=type value=$type><input type=hidden name=rows value=$row><INPUT TYPE=SUBMIT NAME=option VALUE=Update></form></td></tr>\n";
        echo "<tr ><td colspan='5'><form action='propose.php' method='get'><h2><INPUT TYPE=HIDDEN NAME=type VALUE=$type><input type=submit value=\" Offer to $propstr[$type]\"></h2></form></td></tr>\n";
      } else
        echo "<tr ><TD colspan=5>&nbsp;</td></tr>";
      echo "</table>\n";
      if ($type < 4)
        echo "<br><br>\n";
    }
}
HtmlTail();