<?php
    require("basefunc.inc.php");
    /* variables from the environment (GET/POST) */
    extract($_REQUEST, EXTR_SKIP);
     
    session_start();
    if (!array_key_exists("userid", $_SESSION)) {
        header("Location: login.php"); exit();
    }

    extract($_SESSION);
     
    $db = ConnectMysql();
     
    $fields = "firstname, surname, city, country.name as name, arrival, departure, attending, children, travelby, status, kindofaccomodation, nameofaccomodation, logon, email";
    $sql = "SELECT $fields FROM people2,country WHERE (country.id=people2.country) AND (people2.id='$user')";
    if (!$result = mysql_query($sql, $db)) {
        printf("%s<br>", mysql_error($db));
    }
    $row = mysql_fetch_array($result);
    if (!$row) {
      HtmlHead("userview", "User not found", $userstatus, $userid);
      echo "<h3>Sorry, user not found</h3>\n";
      echo "<br>\n";
      HtmlTail();
      exit();
    }
    extract($row);
    HtmlHead("userview", $firstname." ".$surname, $userstatus, $userid);
     
    $sql = "SELECT whois,galpix FROM people2,whois WHERE (people2.id='$user') AND (people2.id=whois.lbwid)";
    if (!$result = mysql_query($sql, $db))
        printf("%s<br>", mysql_error($db));
    $pix = mysql_fetch_array($result);

     
    if ($userid == $user || $userstatus > 8) {
        echo "<table class='reginfo' width=150><tr ><th><A href=useredit.php?user=$user>[Edit this Entry]</a></td></tr></table>\n";
    }
     
    echo "<table class='reginfo'  >\n";
    echo "<tr ><TH COLSPAN=3 ALIGN=CENTER>$firstname $surname</th></tr>\n";
    echo "<tr><td>City</td><td>$city</td>";
    printf("<td width='160' align='center' valign='middle' rowspan='8'>%s</td></tr>\n", ($pix["whois"] > 0)?"<img src='pictures/".$pix["galpix"]."'>":"<br>No<br>Picture<br>Available<br>");
    echo "<tr><td>Country</td><td>$name</td></tr>\n";
    echo "<tr><td>Arriving</td><td>".$date[$arrival]."</td></tr>\n";
    echo "<tr><td>Leaving</td><td>".$date[$departure]."</td></tr>\n";
    printf("<tr><td>Attending</td><td>%s</td></tr>\n", $attending ? "Yes" : "No");
    echo "<tr><td>Children</td><td>$children</td></tr>\n";
    echo "<tr><td>Travelling by</td><td>".$xport[$travelby]."</td></tr>\n";
    if ($userstatus > 8 ) {
	echo "<tr><td>Status</td><td>$status</td></tr>\n";
	echo "<tr><td>Login</td><td>".htmlspecialchars($logon)."</td></tr>\n";
    }
    if (($userstatus > 2) || ($user == $userid)) {
        echo "<tr><td>E-Mail</td><td><A href=mailto:".htmlspecialchars($email).">".htmlspecialchars($email)."</a></td></tr>";
    }
    $accomodationtype = $acctype[$kindofaccomodation];
    $accomodationname = (strlen($nameofaccomodation) > 2) ? $nameofaccomodation: "?";
    printf("<tr><td colspan='3'> Accomodation Type:&nbsp;%s&nbsp;&nbsp;Name:&nbsp;%s</td></tr>\n", htmlspecialchars($accomodationtype), htmlspecialchars($accomodationname));
    echo "</table>";
    echo "<br>\n";

    if ($userstatus > 8 ) {
	?>
	<form method='post' action='upgrade.php'>
	  <input type='hidden' name='lbwid' value='<?php echo $user ?>' />
	  <input type='submit' name='action' value='mark present' class='adminbar' style="width: auto" />
	  <input type='submit' name='action' value='upgrade' class='adminbar' style="width: auto" />
	  <input type='submit' name='action' value='downgrade' class='adminbar' style="width: auto" />
	  <input type='submit' name='action' value='remove' class='adminbar' style="width: auto" />
	</form>
	<?php
    }

    echo "<br>\n";
    HtmlTail();
?>
