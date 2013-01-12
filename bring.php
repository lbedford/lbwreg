<?php
     
    include("basefunc.inc.php");
     
    $_SESSION["userid"] = 0; //stop command line get hacks;
    session_start();
    if (! $_SESSION["userid"] ) {
        header("Location: login.php"); exit();
    }

    extract($_REQUEST, EXTR_SKIP);
     
    $db = connectMysql();
    if (!$option)
        $option = "edit";
     
     
    switch($option) {
        case "add":

	    HtmlHead("bring", "Equipment", $_SESSION["userstatus"], $_SESSION["userid"]);

	    echo "<FORM METHOD=post>\n<INPUT TYPE=HIDDEN NAME=numrows VALUE=4>\n";

	    echo "<table class='reginfo' cellpadding=1 >";
	    echo "<TR VALIGN=TOP ALIGN=CENTER>";
	    echo "<td><b>Count</b></td>";
	    echo "<td><b>Share</b></td>";
	    echo "<td><b>Class</b></td>";
	    echo "<td><b>Sub Class</b><br>e.g. laptop,<br> Net hub/switch,<br>modem/router etc</td>";
	    echo "<td><b>Attr1</b><br>CPU type/speed<br>10 BaseT..<br>56k </td>";
	    echo "<td><b>Attr2:</b><br>NIC type etc</td>";
	    echo "<td><b>Attr3:</b></td>";
	    echo "<td><b>Attr4:</b><br>A number<br>Ports<br>outlets</td>";
	    echo "<tr>\n";
	    for($i = 1; $i <= 4; $i++) {
		echo "<td><input type=text name=number[] size=3></td>";
		echo "<td><select name=share[]><option value=private>No<option value=shared selected>yes</select></td>";
		echo "<td><select name=class[]><option>Computer<option>Network<option>Peripherals<option>Electical Support<option>Beer<option>other</select></td>";
		echo "<td><INPUT type=text size=7 name=type[]></td>";
		echo "<td><INPUT type=text size=7 name=attr1[]></td> ";
		echo "<td><INPUT type=text size=7 name=attr2[]></td>";
		echo "<td><INPUT type=text size=7 name=attr3[]></td>";
		echo "<td><INPUT type=text size=3 name=attr4[]></td>";
		echo "</tr>\n<tr>";
	    }
	    echo "</tr>\n</table>";
	    echo "<INPUT TYPE=SUBMIT name=option value=save><INPUT TYPE=SUBMIT name=option value=cancel></form>";
	    break;

        case "edit":

	    HtmlHead("bring", "Equipment", $_SESSION["userstatus"], $_SESSION["userid"]);

	    echo "<table class='reginfo'  cellpadding=1 >";
	    echo "<TR  VALIGN=TOP ALIGN=CENTER>";
	    echo "<td><b>Count</b></td>";
	    echo "<td><b>Share</b></td>";
	    echo "<td><b>Class</b></td>";
	    echo "<td><b>Sub Class</b><br>e.g.,:laptop,<br> Net hub/switch,<br>modem /pluboard etc</td>";
	    echo "<td><b>Attr1</b><br>Cpu type/speed<br>10 BaseT..<br>56k </td>";
	    echo "<td><b>Attr2:</b><br> NIC type etc</td>";
	    echo "<td><b>Attr3:</b></td>";
	    echo "<td><b>Attr4:</b><br>A number<br>Ports<br>outlets</td>";
	    echo "</tr>\n";
	    echo "<FORM METHOD=POST>\n";
	    $result = mysql_query("SELECT * from equipment where owner=".$_SESSION["userid"]." order by id", $db);
	    $numrows = mysql_num_rows($result);
	    echo "<INPUT TYPE=HIDDEN NAME=numrows VALUE=$numrows>\n";
	    while ($row = mysql_fetch_array($result)) {
		echo "<tr><td><INPUT TYPE=HIDDEN NAME=record[] VALUE=".$row["id"].">\n";

		echo "<input type=text name=number[] size=3 value=".$row["number"]."></td>\n";
		printf("<td><select name=share[]> <option value=private%s> No <option value=shared%s> Yes </select> </td>\n", strcasecmp($row["avail"], "Private")?"":" SELECTED", (strcasecmp($row["avail"], "Shared")?"":" SELECTED"));
		printf("<td><SELECT name=class[]><OPTION %s>Computer<OPTION %s>Network<OPTION %s>Peripherals<OPTION %s>Electrical support<OPTION %s>Beer<OPTION %s>other</select></td>\n", (strcasecmp($row["class"], "Computer")? "" : "SELECTED" ), (strcasecmp($row["class"], "Network")?"":"SELECTED"), (strcasecmp($row["class"], "Peripherals")?"":"SELECTED"), (strcasecmp($row["class"], "Electrical Support")?"":"SELECTED"), (strcasecmp($row["class"], "Beer")?"":"SELECTED"), (strcasecmp($row["class"], "Other")?"":"SELECTED"));
		echo "<td><INPUT type=text size=7 name=type[]  value=\"".$row["type"]."\"></td>\n";
		echo "<td><INPUT type=text size=7 name=attr1[] value=\"".$row["attr1"]."\"></td>\n";
		echo "<td><INPUT type=text size=7 name=attr2[] value=\"".$row["attr2"]."\"></td>\n";
		echo "<td><INPUT type=text size=7 name=attr3[] value=\"".$row["attr3"]."\"></td>\n";
		echo "<td><INPUT type=text size=3 name=attr4[]  value=\"".$row["attr4"]."\"></td>\n";
		echo "</tr>\n";
	    }
	    echo "<tr><TD COLSPAN=8 ALIGN=CENTER><INPUT TYPE=SUBMIT name=option value=update><INPUT TYPE=SUBMIT name=option value=cancel></td></tr>";
	    echo "</table>";

	    break;
        case "update":
	    $err = 0;
	    for($i = 0; $i < $numrows; $i++) {
		if ($number[$i] < 1) {
		    $sql = "DELETE FROM equipment WHERE id='$record[$i]'" ;
		} else {
		    $sql = "UPDATE equipment set avail = '$share[$i]', number = '$number[$i]',class = '$class[$i]',type = '$type[$i]', attr1='$attr1[$i]', attr2='$attr2[$i]',attr3= '$attr3[$i]',attr4= '$attr4[$i]' WHERE id=$record[$i]";
		}
		// printf("%s<br>",$sql);
		if (!mysql_query($sql, $db)) {
		    printf("%s<br>%s<br><br>", $sql, mysql_error($db));
		    $err++;
		}
	    }
	    if ($err == 0)
		header("Location: bring.php?option=quickview");
	    break;
        case "save":
	    $err = 0;
	    for ($i = 0; $i < $numrows; $i++) {
		if ($number[$i] < 1) {

		    continue;
		}

// TODO(lbedford): escape this
		$sql = "INSERT INTO equipment (owner, avail, number, class, type, attr1, attr2, attr3, attr4) VALUES (".$_SESSION["userid"].", '$share[$i]', '$number[$i]', '$class[$i]', '$type[$i]', '$attr1[$i]', '$attr2[$i]', '$attr3[$i]', '$attr4[$i]')";
		if (!mysql_query($sql, $db)) {
		    printf("%s<br>%s<br><br>", $sql, mysql_error($db));
		    $err++;
		}
	    }
	    if ($err > 0)
		break;

        case "quickview":
	    header("Pragma: no-cache");

	    HtmlHead("equipment", "Equipment List", $_SESSION["userstatus"] , $_SESSION["userid"]);
	    echo "";
	    //       echo "<h2><a href=welcome.php>[Home]</a><a href=userbrowse.php>[Who is coming]</a><A href=activities.php>[Activities]</a></h2>";
	    echo "<table class='reginfo' width=90% cellpadding=1  >";
	    echo "<tr ><TH COLSPAN=4>Equipment grouped by Class and type</th></tr>";
	    echo "<tr><th>Number</th><th>Class</th><th>Type</th><th> Other descriptions</th></tr>";
	    $result = mysql_query("SELECT class, type,attr1, attr2,attr3,attr4, sum(number) as qty from equipment group by class,type", $db);
	    while ($row = mysql_fetch_array($result))
	    printf("<tr><td>%s</td><td>%s</td><td>%s&nbsp;</td><td>%s %s %s&nbsp;</td></tr>\n", $row["qty"], $row["class"], $row["type"], $row["attr1"], $row["attr2"], $row["attr3"], $row["attr4"]);
	    echo "<tr ><TD ALIGN=CENTER COLSPAN=4><A HREF=bring.php?option=add>[Add to List]</a>&nbsp;<a href=bring.php?option=edit>[Edit List]</a></td></tr>";
	    echo "</table>";
	    break;
        case "fullview":
	    header("Pragma: nocache");

	    echo "";
	    echo "<h2><a href=welcome.php>[Home]</a><a href=userbrowse.php>[Who is coming]</a><A href=activities.php>[Activities]</a></h2>";
	    echo "<table class='reginfo' width=90% cellpadding=1  >";
	    echo "<tr ><TH COLSPAN=7>Equipment grouped by Class and type</th></tr>";
	    echo "<tr><th>Number</th><th>Class</th><th>Type</th><th>a1</th><th>a2</th><th>a3</th><th>a4</th></tr>";
	    $result = mysql_query("SELECT * from equipment", $db);
	    while ($row = mysql_fetch_array($result))
	    printf("<tr><td>%s</td> <td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td></tr>\n", $row["number"], $row["class"], $row["type"], $row["attr1"], $row["attr2"], $row["attr3"], $row["attr4"]);
	    echo "<tr ><TD ALIGN=CENTER COLSPAN=7>You may edit your equipment list from your welcome page</td></tr>";
	    echo "</table>";
	    echo "<h2><a href=welcome.php>[Home]</a><a href=userbrowse.php>[Who is coming]</a><A href=activities.php>[Activities]</a><A href=equipment.php>[Equipment]</a></h2>";
	    break;
        case "cancel":
	    header("Location: welcome.php");
	    exit();
    }
    HtmlTail();
     
?>
