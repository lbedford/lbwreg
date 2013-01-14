<?php # -*- php -*-

    require("dbconnect.inc.php");

    require("config.php");

    require_once("lib/template.class.php");

    date_default_timezone_set($timeZone);

    $date[NULL] = "undecided";

    # The days of the event
    $start_time = strtotime("-1 day", strtotime("$year-$month-$day"));
    for ($i = 0; $i <= $duration + 1; $i++)
    {
      $timestamp = strtotime("+ $i day", $start_time);
      $timestamps[$i] = $timestamp;
      $date[$i] = date("j F", $timestamp);
      $shortday[$i] = date("D", $timestamp);
      $weekday[$i] = date("l", $timestamp);
    }

     
    # ----------------------------------------------------------------------
     
    function HtmlHead($page, $title, $status, $userid, $javascript = '') {
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
        echo "<html>\n";
        echo "<head>\n";
        include("html/head.html");
        echo "  <link rel=\"stylesheet\" href=\"reg.css\" type=\"text/css\" />\n";
        if (!empty($javascript)) {
          echo "$javascript";
        }
        echo "</head>\n";
        echo "<body class='protected'>\n";
        echo "  <div class='content' >\n";
        echo "    <table border='0' cellpadding='0' width='100%'>\n";
        include("html/header.html");
        echo "      <tr>\n";
        echo "        <td class='leftcolumn strut' valign='top'>\n";
        echo "          <br />\n";
        echo "          $title";
        echo "        </td>\n";
        echo "        <td valign='top' class='centercolumn'><center>\n";

        echo "          <div class='subnavbar' >\n";
        global $menu, $admin_menu;
        Template::menu($menu, $page, $status);
        echo "          </div>\n";

        if ($status >= 8) {
          echo "          <div class='adminbar' >\n";
          Template::menu($admin_menu, $page, $status);
          echo "</div>\n";
        }
        echo "<br />";

        return 1;
    }
     
     
    function HtmlTail() {
      global $teammail;
    ?>
      <table class='footer' width='100%'>
	<tr valign='bottom'>
	    <td class='leftnote'></td>
	    <td class='rightnote'> &nbsp;&nbsp; </td>
	</tr>
	<tr valign="bottom">
	    <td class="leftnote">
	    </td>
	    <td class="rightnote">
		<script type="text/javascript" language="JavaScript1.1"><!--
		showAddr("", "<?php echo $teammail ?>", "The Organising Team");;
		//--></script>&nbsp;&nbsp;
	    </td>
	</tr>
      </table>
    </center></td></tr></table>
  </div>
</body>
</html>

    <?php
    }
     
    function GetCountry($country, $db) {
        $resp = mysql_query("SELECT * FROM country WHERE id LIKE '$country'", $db);
        $myrow = mysql_fetch_array($resp);
        return $myrow["name"];
    }
     
    function GetlbwUser($geekid, $db) {
        if ($geekid < 0) {
            $geekname[0] = "Unknown";
            $geekname[1] = "";
            $geekname[2] = "";
        } else {
            $q = "SELECT * from people2 where id='$geekid'";
            $r = mysql_query($q, $db);
            $nv = mysql_fetch_array($r);
            $geekname[0] = $nv["firstname"];
            $geekname[1] = $nv["surname"];
            $geekname[2] = $nv["email"];
        }
        return $geekname;
    }
     
    function Event2sched($row) {
	global $shortday;
        global $timestamps;
	if ($row["type"] == 1)
	    $sched = "All Week";
	else {
	    $evday = $row["day"];
	    if ($evday < 1)
		$sched = "Not yet scheduled";
	    else {
		$hr = $row["hour"];
                $starttimestamp = $timestamps[$evday] + ($hr * 3600);
                $endtimestamp = $starttimestamp + ($row["duration"] * 3600);
                $endformat = "H:i";
                if (date("l", $starttimestamp) != date("l", $endtimestamp)) {
                  $endformat = "l ".$endformat;
                }
                $end = $hr + $row["duration"];
		if ($hr < 10) $hr = "&ensp;$hr";
                $sched = date("l j F H:i", $starttimestamp)." - ".date($endformat, $endtimestamp);
	    }
	}
	return $sched;
    }

    function text2html($text) {
	
	$text = preg_replace("/^/", "<p>", $text);

	$text = preg_replace("/\n\r?(\n\r?)+/", "\n</p>\n<p>\n", $text);
	$text = preg_replace("/[^='\"](https?:\/\/[^ \t\n\r>]+)/", " <a href='$1'>$1</a><br />", $text);
	
	$text = preg_replace("/$/", "</p>", $text);

	return $text;
    }

    function getTshirtPrice($Prices, $quantity) {
      foreach($Prices as $threshold => $Price) {
        if (intval($quantity) >= $threshold) {
          $p = $Price;
        } 
      }

      return $p;
    }

    function getForumOwnerEmail($forum) {
      global $db;
      $query = "select people2.email from people2, Events where ".
               "Events.id = $forum and people2.id = Events.owner";
      $resp = mysql_query($query, $db);
      if (!$resp) {
          printf("<br />$query: %s<br />",mysql_error($db));
      }
      $myrow = mysql_fetch_array($resp);
      return $myrow["email"];
    }

    function getForumOwnerName($forum) {
      global $db;
      $query = "select people2.firstname, people2.surname from people2, Events where ".
               "Events.id = $forum and people2.id = Events.owner";
      $resp = mysql_query($query, $db);
      if (!$resp) {
          printf("<br />$query: %s<br />",mysql_error($db));
      }
      $myrow = mysql_fetch_array($resp);
      return $myrow["firstname"]." ".$myrow["surname"];
    }

    function getNameOfEvent($event) {
      global $db;
      $query = "select name from Events where id = $event";
      $resp = mysql_query($query, $db);
      if (!$resp) {
          printf("<br />$query: %s<br />",mysql_error($db));
      }
      $myrow = mysql_fetch_array($resp);
      return $myrow["name"];
    };

    function getUsername($user) {
      global $db;
      $query = "select firstname, surname from people2 where id = $user";
      $resp = mysql_query($query, $db);
      if (!$resp) {
          printf("<br />$query: %s<br />",mysql_error($db));
      }
      $myrow = mysql_fetch_array($resp);
      return $myrow["firstname"]." ".$myrow["surname"];
    };

    function getListOfUsersForEvent($event) {
      global $db;
      $query = "select concat(people2.firstname, ' ', people2.surname) from ".
               "people2, eventreg where eventreg.geek = people2.id ".
               "and event = $event";
      $resp = mysql_query($query, $db);
      if (!$resp) {
          printf("<br />$query: %s<br />", mysql_error($db));
      }
      $users = array();
      while ($myrow = mysql_fetch_row($resp)) {
        array_push($users, $myrow[0]);
      };
      return implode($users, ",");
    };

    function getDescriptionOfEvent($event) {
      global $db;
      $query = "select description from Events where id = $event";
      $resp = mysql_query($query, $db);
      if (!$resp) {
          return "";
      }
      $myrow = mysql_fetch_array($resp);
      return $myrow["description"];
    };
?>
