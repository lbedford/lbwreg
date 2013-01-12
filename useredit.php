<?php
    require("basefunc.inc.php");

    $db = ConnectMysql();

    extract($_REQUEST, EXTR_SKIP);
    
    session_start();

    extract($_SESSION);

    if (! $userid ) {
        header("Location: login.php"); exit();
    }

    if (!isset($user)) {
      $user = $userid;
    }

    if ( !($userid == $user || $userstatus == 16 )) {
      HtmlHead("useredit", "Illegal Action", "", "");
      printf("You're not allowed edit this user's information<br><ul>\n");
      HtmlTail();
      exit();
    }
     
    if (!isset($LBWID)){
        $sql = "SELECT * FROM people2 WHERE id='$user'";
        $result = mysql_query($sql, $db);
        $row = mysql_fetch_array($result);
         
        HtmlHead("useredit", "User Information", $userstatus, $user);
         
        printf("\n<FORM METHOD=POST><INPUT TYPE=HIDDEN NAME=LBWID VALUE=$user>\n");
        printf("<table class='reginfo' CELLPADDING=2>\n");
        printf("<tr><td>Login     </td><td><INPUT TYPE=TEXT name=logon VALUE = \"%s\" SIZE=35 MAXLEN=40></td></tr>\n", $row["logon"]);
        printf("<tr><td>First Name</td><td> <INPUT TYPE=TEXT name=firstname VALUE = \"%s\" SIZE=35 MAXLEN=40></td></tr>\n", $row["firstname"]);
        printf("<tr><td>Surname</td><td><INPUT TYPE=TEXT name=surname VALUE = \"%s\" SIZE=35 MAXLEN=40></td></tr>\n", $row["surname"]);
        printf("<tr><td>Email</td><td><INPUT TYPE=TEXT name=email VALUE= \"%s\" SIZE=35 MAXLEN=60></td></tr>\n", $row["email"]);
        printf("<tr><td>City</td><td><INPUT TYPE=TEXT name=city VALUE=\"%s\" SIZE=35 MAXLEN=40></td></tr>\n", $row["city"]);
        printf("<tr><td>Country</td><td><SELECT name=country>");
        $sql = "SELECT * FROM country order by name";
        $rx = mysql_query($sql, $db);
        while ($pays = mysql_fetch_array($rx)) {
            $sel = ($pays["id"] == $row["country"])?"SELECTED":
            "";
            printf("<OPTION VALUE=%d %s>%s\n", $pays["id"], $sel, $pays["name"]);
        }
        printf("</select></td></tr>\n");

        //printf("<tr><td>Number of adults including yourself<br>(0 if you can not come)</td><td><INPUT TYPE=TEXT name = adults VALUE=\"%s\" SIZE=4 MAXLEN=4></td></tr>\n", $row["adults"]);

        printf("<tr><td>number of children</td><td><INPUT TYPE=TEXT name = children VALUE=\"%s\" SIZE=4 MAXLEN=4></td></tr>\n", $row["children"]);
        printf("<tr><td>Arrival</td><td><SELECT name = arrival>");
        for ($i = 0; $i < count($date); $i++) {
            $sel = ($row["arrival"] == $i)?"SELECTED":
            "";
            printf("<OPTION VALUE=%d %s>%s\n", $i, $sel, $date[$i]);
        }
        printf("</select></td></tr>\n");
        printf("<tr><td>Departure</td><td><SELECT name = departure>");
        for ($i = 0; $i < count($date); $i++) {
            $sel = ($row["departure"] == $i) ? "SELECTED" :
            "" ;
            printf("<OPTION VALUE=%d %s>%s\n", $i, $sel, $date[$i]);
        }
        printf("</select></td></tr>\n");
         
        printf("<tr><td>Travelling by</td><td><SELECT name = travelby>");
        for ($i = 0; $i < count($xport); $i++) {
            $sel = ($i == $row["travelby"])? "SELECTED":
            "";
            printf("<OPTION VALUE = %s %s>%s\n", $i, $sel, $xport[$i]);
        }
        printf("</select></td></tr>\n");
        printf("<tr><td>Kind of Accommodation</td><td><SELECT name = kindofaccomodation>\n");
        for ($i = 0; $i < count($acctype); $i++) {
            $sel = ($accorder[$i] == $row["kindofaccomodation"])? "SELECTED":
            "";
            printf("<OPTION VALUE = %s %s>%s\n", $i, $sel, $acctype[$accorder[$i]]);
        }
        printf("</select></td></tr>\n");
         
        printf("<tr><td>Name of Accomodation<br>if known</td><td><INPUT TYPE=TEXT name = nameofaccomodation VALUE=\"%s\" SIZE=35 MAXLEN=40></td></tr>\n", $row["nameofaccomodation"]);

        printf("<tr><td>Attending?</td><td><INPUT TYPE=CHECKBOX name=attending VALUE=\"1\" %s></td></tr>\n", $row["attending"] == 1 ? "checked=checked" : "" );
         
        printf("<tr><TD  ALIGN=LEFT><INPUT TYPE=SUBMIT NAME=submit VALUE=SAVE></td><TD ALIGN=RIGHT><INPUT TYPE=SUBMIT NAME=submit VALUE=CANCEL></td></tr>\n");
        printf("</table>\n</form>\n");
        HtmlTail();
        exit();
    } else {
        $LBWID = mysql_real_escape_string(trim($LBWID));
        if (isset($submit)) {
          $submit = mysql_real_escape_string(trim($submit));
        }
        if (isset($logon)) {
          $logon = mysql_real_escape_string(trim($logon));
        }
        if (isset($firstname)) {
          $firstname = mysql_real_escape_string(trim($firstname));
        }
        if (isset($surname)) {
          $surname = mysql_real_escape_string(trim($surname));
        }
        if (isset($email)) {
          $email = mysql_real_escape_string(trim($email));
        }
        if (isset($city)) {
          $city = mysql_real_escape_string(trim($city));
        }
        if (isset($country)) {
          $country = mysql_real_escape_string(trim($country));
        }
        if (isset($attending)) {
          $attending = 1;
        } else {
          $attending = 0;
        }
        if (isset($children)) {
          $children = mysql_real_escape_string(trim($children));
        }
        if (isset($arrival)) {
          $arrival = mysql_real_escape_string(trim($arrival));
        }
        if (isset($departure)) {
          $departure = mysql_real_escape_string(trim($departure));
        }
        if (isset($travelby)) {
          $travelby = mysql_real_escape_string(trim($travelby));
        }
        if (isset($kindofaccomodation)) {
          $kindofaccomodation = mysql_real_escape_string(trim($kindofaccomodation));
        }
        if (isset($nameofaccomodation)) {
          $nameofaccomodation = mysql_real_escape_string(trim($nameofaccomodation));
        }
        if ($submit == 'CANCEL') {
            header("Location: welcome.php");
            exit();
        }
        if (!($LBWID == $userid || $userstatus == 16)) {
	    HtmlHead("useredit", "User Edit", "", "");
            printf("There seems to be a SNAFU!<br>Session details are missing at bottom<br>");
            printf("<A HREF=login.php>Continue</a>\n");
            HtmlTail();
        }
        $err = 0;
        if (($departure < $arrival) && ($departure != 1)) {
            $err++;
            $error[$err] = "You can not leave before you arrive";
        }
        //more error checking
        $result = mysql_query("SELECT logon FROM people2 where id=$LBWID", $db);
        $row = mysql_fetch_array($result);
        if ($row["logon"] != $logon) {
            if (strlen($logon) < 4) {
                echo "login must be at least 4 letters<br>\n";
                $err++;
            }
            $result = mysql_query("SELECT id,logon FROM people2 WHERE (logon LIKE '$logon') AND (id != $LBWID)", $db);
            if (!$result) {
                echo mysql_error($db)."<br>\n";
                exit();
            }
            if (mysql_num_rows($result) > 0) {
                $err++;
                echo "Login \"".$logon."\" is already in use<br>\n";
            }
        }
        if ($err == 0) {
            $sql = "UPDATE people2 SET logon = '$logon',email = '$email', city='$city', country=$country, attending=$attending, children='$children', arrival=$arrival, departure=$departure, travelby='$travelby', kindofaccomodation='".$accorder[$kindofaccomodation]."', nameofaccomodation='$nameofaccomodation', firstname='$firstname', surname='$surname'  WHERE id='$LBWID'";
            $result = mysql_query($sql, $db);
            if ($LBWID == 32) {
              $myfile = "/tmp/log.txt";
              $fh = fopen($myfile, 'w+');
              fwrite($fh,$sql);
              fclose($fh);
            }
            if (!$result) {
		HtmlHead("useredit", "Database Error", $userstatus, $userid);
                printf("%s", mysql_error());
                HtmlTail();
                exit();
            }
            header("Location: userview.php?user=$LBWID");
            exit();
        }
        HtmlHead("useredit", "Inconsistent data", "", "");
        printf("Your Form contains inconsistent data<br><ul>\n");
        for ($i = 1; $i <= $err; $i++)
        printf("<li>%s<li>\n", $error[$i]);
        printf("</ul><br><A HREF=useredit.php>CONTINUE</a><br>\n");
        HtmlTail();
    }
     
?>
