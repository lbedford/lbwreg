<?php
    include("basefunc.inc.php");

    /* variables from the environment (GET/POST) */
    /* (none found) */

    $_SESSION["userid"] = 0;
    session_start();
    if (! $_SESSION["userid"] ) 
    {
        header("Location: login.php"); exit();
    }
     
    $db = connectMysql();

    HtmlHead("schedule", "Provisional Schedule", 
        $_SESSION["userstatus"], $_SESSION["userid"]);
     
    if ($_SESSION["userstatus"] == 16) 
    {
        echo "Numbers in brackets after event names indicate:<br>";
        echo "(number of people with conflicts / number of participants)<br>";
        echo "Conflicts are only counted for events further to the right.<br>";
    }
    else 
    {
        echo "Numbers in brackets after events names".
             " indicate the number of participants.<br>";
    }

    $att = 0;
    for($day = 1; $day < count($date) - 2; $day++) 
    {
        $forum_duration = array();
        $evid = array();
        $name = array();
        $hour = array();
        $name = array();
        $sched = array();

        // run through every day
        for ($i = 0; $i < 24; $i++)
        {
            // initialize every hour
            $sched[$i] = 0;
        }

        $event_sql = "SELECT id,schedtxt,hour,forum_duration,type,tslot ".
            "FROM Events where (day=$day) ORDER BY hour,forum_duration,tslot";
        $result = mysql_query($event_sql, $db);
        $name[0] = "&nbsp;";
        $name[25] = "...continued";
	$name[26] = "Lunch";
	$name[27] = "<font color=red>*** Conflict ***</font>";
        $evt = 2;
        $late = 0;
        while ($row = mysql_fetch_array($result)) 
        {
            $evt++;
            $h = $hour[$evt] = $row["hour"];
            $name[$evt] = "<a href=forum.php?forum=".
                $row["id"].">".$row["schedtxt"]."</a>";
            $evid[$evt] = $row["id"];
            $dur = $forum_duration[$evt] = $row["forum_duration"];

	    if      ($dur < 3) $slottype = 1; // One hour slot(s)
	    else if (($dur == 5) && ($h != 9) && ($h != 13)) 
                $slottype = 3; // Half day afternoon, or whole day
	    else if ($dur < 6) $slottype = 2; // Half day slot
	    else	       $slottype = 4; // Whole day slot

            if ($h > 17) 
            {
                $late++;
                $slot=12+$late;
            }
            else 
            {
                switch($slottype) 
                {
                  case 1: // find the right 1 hour slot
                    $slot=$h-8;
                    break;
                  case 2: // find the right 1/2 day slot
                    if ($h < 13)
                        $slot=10;
                    else
                        $slot=11;
                    break;
                  case 3: // half afternoon, or whole day
                    if ($h < 13)
                        $slot=12;
                    else
                        $slot=11;
                    break;
                  case 4: // it's a full day
                    $slot=12;
                    break;
                }
            }
            // if there's something in this slot already
            // mark it as a conflict
            if ($sched[$slot])
                $sched[$slot]=27;
            else
                $sched[$slot]=$evt;

            // if it's two hours, continue it in the next slot
            if ($dur == 2) $sched[$slot+1]=25;
        };

        if      ( ! $sched[4] ) $sched[4] = 26;
        else if ( ! $sched[5] ) $sched[5] = 26;

        echo "<br>";

        // total up the number of people arriving today
        $result = mysql_query("SELECT sum(attending+children) as arrs ".
                              "FROM people2 where arrival = $day", $db);
        $row = mysql_fetch_array($result);
        $arr = $row["arrs"];
        $arr = $arr ? $arr : 0;

        // total up the number of people departing today
        $result = mysql_query("SELECT sum(attending+children) as deps ".
                              "FROM people2 where departure = $day", $db);
        $row = mysql_fetch_array($result);
        $dep = $row["deps"];
        $dep = $dep ? $dep : 0;

        // and figure out the number of attendees
        $att = $att + $arr - $dep;

        echo "<table class='reginfo' width=100% ><tr ><th colspan=4>".
            $weekday[$day]." ".$date[$day]." ($arr arrival";
        if ($arr!=1) echo "s";
        echo "; $dep departure";
        if ($dep!=1) echo "s";
        echo "; $att attendees)</th></tr>\n";
        echo "<tr ><th width=10%>Time</th>\n";
        echo "<th width=30%>Short events</th>\n";
        echo "<th width=30%>Half day events</th>\n";
        echo "<th width=30%>Day long events</th></tr>";

        $morning=$name[$sched[10]];
        $afternoon=$name[$sched[11]]; 
        $allday=$name[$sched[12]];
        if ($_SESSION["userstatus"] == 16) {
            for ($evnum=1; $evnum<5; $evnum++) {
                if(($sched[$evnum] != 0) && ($sched[$evnum] < 25))  {
                    $e1 = $sched[10] ? $evid[$sched[10]]:0;
                    $e2 = $sched[12] ? $evid[$sched[12]]:0;
                    $result = mysql_query("SELECT * FROM eventreg as e1, eventreg as e2 WHERE e1.geek=e2.geek AND e1.event=".$evid[$sched[$evnum]]." AND (e2.event=$e1 OR e2.event=$e2)", $db);
                    $num1 = mysql_num_rows($result);
                    $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[$evnum]], $db);
                    $num2 = mysql_num_rows($result);
                    $name[$sched[$evnum]] .= " ($num1/$num2)";
                }
            }
            for ($evnum=5; $evnum<10; $evnum++) {
                if(($sched[$evnum] != 0) && ($sched[$evnum] < 25))  {
                    $e1 = $sched[11] ? $evid[$sched[11]]:0;
                    $e2 = $sched[12] ? $evid[$sched[12]]:0;
                    $result = mysql_query("SELECT * FROM eventreg as e1, eventreg as e2 WHERE e1.geek=e2.geek AND e1.event=".$evid[$sched[$evnum]]." AND (e2.event=$e1 OR e2.event=$e2)", $db);
                    $num1 = mysql_num_rows($result);
                    $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[$evnum]], $db);
                    $num2 = mysql_num_rows($result);
                    $name[$sched[$evnum]] .= " ($num1/$num2)";
                }
            }
            if($sched[10] != 0) {
                if ($sched[12] != 0) {
                    $result = mysql_query("SELECT * FROM eventreg as e1, eventreg as e2 WHERE e1.geek=e2.geek AND e1.event=".$evid[$sched[10]]." AND e2.event=".$evid[$sched[12]], $db);
                    $num1 = mysql_num_rows($result);
                }
                else {
                    $num1 = 0;
                }
                $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[10]], $db);
                $num2 = mysql_num_rows($result);
                $morning .= " ($num1/$num2)";
            }
            if($sched[11] != 0) {
                if ($sched[12] != 0) {
                    $result = mysql_query("SELECT * FROM eventreg as e1, eventreg as e2 WHERE e1.geek=e2.geek AND e1.event=".$evid[$sched[11]]." AND e2.event=".$evid[$sched[12]], $db);
                    $num1 = mysql_num_rows($result);
                }
                else {
                    $num1 = 0;
                }
                $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[11]], $db);
                $num2 = mysql_num_rows($result);
                $afternoon .= " ($num1/$num2)";
            }
            if($sched[12] != 0) {
                $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[12]], $db);
                $num2 = mysql_num_rows($result);
                $allday .= " ($num2)";
            }
            for ($evnum=13; $evnum<20; $evnum++) {
                if(($sched[$evnum] != 0) && ($sched[$evnum] <25)) {
                    $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[$evnum]], $db);
                    $num2 = mysql_num_rows($result);
                    $name[$sched[$evnum]] .= " ($num2)";
                }
            }
        }
        else {
            for ($evnum=1; $evnum<20; $evnum++) {
                if(($sched[$evnum] != 0) && ($sched[$evnum] <25)) {
                    $result = mysql_query("SELECT * FROM eventreg WHERE event=".$evid[$sched[$evnum]], $db);
                    $num2 = mysql_num_rows($result);
                    $name[$sched[$evnum]] .= " ($num2)";
                }
            }
            $morning=$name[$sched[10]];
            $afternoon=$name[$sched[11]]; 
            $allday=$name[$sched[12]];
        }
        if (($morning != "&nbsp;") && (($hour[$sched[10]] != 9) || ($forum_duration[$sched[10]] != 4))) {
            $morning.="<br>(".($hour[$sched[10]]).":00-".($hour[$sched[10]]+$forum_duration[$sched[10]]).":00)";
        }
        if (($afternoon != "&nbsp;") && (($hour[$sched[11]] != 14) || ($forum_duration[$sched[11]] != 4))) {
            $afternoon.="<br>(".($hour[$sched[11]]).":00-".($hour[$sched[11]]+$forum_duration[$sched[11]]).":00)";
        }
        if (($allday != "&nbsp;") && (($hour[$sched[12]] != 9) || ($forum_duration[$sched[12]] != 8))) {
            $allday.="<br>(".($hour[$sched[12]]).":00-".($hour[$sched[12]]+$forum_duration[$sched[12]]).":00)";
        }
        echo "<tr><td>0900-1000</td><td align=center>".$name[$sched[1]]."</td><TD rowspan=4 align=center>".$morning."</td><TD rowspan=9 align=center>".$allday."</td></tr>\n";
        echo "<tr><td>1000-1100</td><td align=center>".$name[$sched[2]]."</td></tr>\n";
        echo "<tr><td>1100-1200</td><td align=center>".$name[$sched[3]]."</td></tr>\n";
        echo "<tr><td>1200-1300</td><td align=center>".$name[$sched[4]]."</td></tr>\n";
        echo "<tr><td>1300-1400</td><td align=center>".$name[$sched[5]]."</td><td>&nbsp;</td></tr>\n";
        echo "<tr><td>1400-1500</td><td align=center>".$name[$sched[6]]."</td><TD rowspan=4 align=center>".$afternoon."</td></tr>\n";
        echo "<tr><td>1500-1600</td><td align=center>".$name[$sched[7]]."</td></tr>\n";
        echo "<tr><td>1600-1700</td><td align=center>".$name[$sched[8]]."</td></tr>\n";
        echo "<tr><td>1700-1800</td><td align=center>".$name[$sched[9]]."</td></tr>\n";
        for ($slot = 13; $sched[$slot] && ($slot < 24); $slot++) {
            $evt = $sched[$slot];
            echo "<tr><td> ".$hour[$evt]."00 - ??? </td><TD colspan=3 align=center>".$name[$evt]."</td></tr>";
        }
        printf("</table>\n");
    }
    HtmlTail();
?>
