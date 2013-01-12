<?php
  include("basefunc.inc.php");
     
  function finddups($db, $logon, $firstname, $surname, $email) {
    $result = mysql_query("SELECT id, status FROM people2 WHERE (".
        "((surname like '$surname') and (firstname = '$firstname')) OR ".
        "((surname = '$firstname') AND (firstname = '$surname')) or ".
        "(email = '$email') or (logon='$logon'))", $db);

    if (!$result) {
      echo mysql_error($db)."<br>\n";
      exit();
    }

    if (mysql_num_rows($result) == 0) {
      return 0;
    }

    $row = mysql_fetch_row($result);

    HtmlHead("Register", "Register?", $row["status"], $row["id"]);
    echo "<h2>You appear to be registered already</h2>";
    echo "<br><hr><br>If you have forgotten your password please email <a href=mailto:$teammail>The Team</a><br><hr><br>\n";
    echo "<A href=login.php>[LOGIN]</a>or <A href=\"$eventhost/\">[LBW$year Main Site]</a>";
    exit();
  }
     
  $db = ConnectMysql();
    
  extract($_REQUEST, EXTR_SKIP);

  if (isset($submit)) {
    switch ($submit) {
      case "Register":
        $firstname = mysql_real_escape_string(trim($firstname));
        $surname = mysql_real_escape_string(trim($surname));
        $logon = mysql_real_escape_string(trim($logon));
        $password = mysql_real_escape_string($password);
        $email = mysql_real_escape_string(trim($email));
        $city = mysql_real_escape_string(trim($city));
        $country = mysql_real_escape_string($country);
        $noadults = mysql_real_escape_string($noadults);
        $nochildren = mysql_real_escape_string($nochildren);
        $arrival = mysql_real_escape_string($arrival);
        $departure = mysql_real_escape_string($departure);
        $travelby = mysql_real_escape_string($travelby);
        $accomodation = mysql_real_escape_string($accomodation);
        $accname = mysql_real_escape_string(trim($accname));

        $err = 0;
        $error = "";
        if (($departure != 0) && ($departure < $arrival)) {
          $err++;
          $error .= "<li>You can not leave before you arrive</li>";
        }

        if ($country == "-1") {
          $err++;
          $error .= "<li>It's doubtful you know where you live</li>";
        }

        if (!$password) {
          $err++;
          $error .= "<li>You must supply a password</li>";
        }

        if (strlen($password) < 4) {
          $err++;
          $error .= "<li>Password must be at least 4 characters</li>";
        }

        if (strlen($logon) < 4) {
          $err++;
          $error .= "<li>Login must be at least 4 characters</li>";
        }

        if ((strlen($email) < 4) || !strpos($email, "@")) {
          $err++;
          $error .= "<li>You must supply a valid email address</li>";
        }

        if (strlen($firstname) < 2) {
          $err++;
          $error .= "<li>You must supply your firstname</li>";
        }

        if (strlen($surname) < 2) {
          $err++;
          $error .= "<li>You must supply your surname</li>";
        }
        $status = 1;

        //more error checking
        if ($err == 0) {
          finddups($db, $logon, $firstname, $surname, $email);

          $email = addslashes($email);
          $sql = "INSERT INTO  people2 (firstname, surname,logon,password,email, city, country, attending, children, arrival, departure, travelby, kindofaccomodation, nameofaccomodation,status) VALUES (";
          $sql .= "'$firstname','$surname','$logon','$password','$email', '$city', $country, '$noadults', '$nochildren',$arrival , $departure, '$travelby', '$accomodation', '$accname','$status')";
          $result = mysql_query($sql, $db);
          if (!$result) {
            HtmlHead("Register", "MySql Error", "", "");
            printf("%s<br>%s", $sql, mysql_error());
            HtmlTail();
            exit ;
          }
          $id = mysql_insert_id($db);
          if ($status > 1)
            header("Location: verify.php?logon=".urlencode($logon)."&password=".urlencode($password));

            HtmlHead("register", "Successful Registration", "", "");

            echo "<h1>Thank you for Registering</h1><br><hr><br>";
            echo "You now have access to none of the registration site.<br>";
            echo "Full access should be enabled by the end of the day.<br>";
            echo "<a href=$eventhost/>Continue</a>";
            HtmlTail();
            mail($teammail, "New LBW $year registration",
                 "New registration:\n\n".
                 "  Name: $firstname $surname\n".
                 "  Email: $email\n".
                 "  Location: $city\n".
                 "  Attendees: $noadults + $nochildren\n".
                 "  IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n\n".
                 "Please check that email etc. is reasonable, and upgrade to level 4:\n".
                 "  $regpath/users.php\n\n".
                 "Regards,\n\n".
                 "  The LBW $year registration site", "");

            exit();
        }

        HtmlHead("Register", "Registration Error", "", "");
        echo "<h2> There were errors in your Registration</h2><hr>" ;
        echo "<ul><br>\n$error</ul><br>";
        echo "click on 'previous page' on your browser or <a href=register.php> click here to restart<br>\n";
        HtmlTail();
        exit();

      case "CANCEL":
        header("Location: ".$eventhost."/");
        break; 
    }
  }
      
  HtmlHead("Register", "Register for LBW $year", "", "");

  $db = ConnectMysql();
  ?>
      <p>

  If you plan to come to LBW <?php echo $year ?>, please
  register with this form. This way, we will have some idea of
  how many people we can expect. This is for informational
  purposes only.

      </p>
      <p>

  All participants can use this registration system to see who
  else is coming. There is NO money involved with registering.
  If you change your mind and decide not to come after all,
  please remember to remove yourself from the registration here,
  so we can estimate correct numbers of attendees.

      </p>
      <p>

  It is best for each attending adult to register individually,
  rather than registering multiple adults under one name,
  as this enables everyone to sign up for talks, hikes and other
  events; it also helps to avoid duplication if two members of a
  group end up registering the entire group under their own
  names.

      </p>
      <p>

  When you have completed the registration an email will be sent
  to you with a username and a password that you can use to
  login and browse the complete list of the participants and
  activities in the LBW.

      </p>
      <p>

  You can change all data later on as well as unregister
  yourself from the event.

      </p>
      <hr />
      <form method="post" action="register.php">
        <table class='reginfo'>
          <tr><td>First name:</td><td><INPUT name="firstname"></td> </tr>
          <tr><td>Surname:</td><td><INPUT name="surname"></td></tr>
          <tr><td>Login:</td><td><INPUT name="logon"></td></tr>
          <tr><td>Password:</td><td><INPUT type="password" name="password"></td></tr>
          <tr><td>E-Mail:</td><td><INPUT name="email"></td></tr>
          <tr><td>City:</td><td><INPUT name="city"></td></tr>
          <tr><td> Country: </td>
            <td>
              <select name="country">
                <?php
                  printf("\t  <option value='-1'>Zweifelland</option>\n");
                  $myQuery = mysql_query("SELECT * FROM country ORDER BY name", $db);
                  while ($myRow = mysql_fetch_array($myQuery)) {
                    printf("\t  <option value='%s'>%s</option>\n", $myRow["id"], $myRow["name"]);
                  }
                ?>
              </select>
            </td>
          </tr>
          <tr><td>How many adults does this registration cover, <i>including yourself:</i></td>
          <td>
            <select name="noadults">
              <option selected> 0</option>
              <option>1</option>
            </select>
          </td>
        </tr>
        <tr><td> How many children:</td>
          <td>
            <select name="nochildren">
              <option selected> 0</option>
              <option >1</option>
              <option >2</option>
              <option >3</option>
              <option >4</option>
              <option >5</option>
            </select>
          </td>
        </tr>
        <tr><td>Arrival date:</td>
          <td>
            <select name="arrival">
              <?php
                for($i = 0; $i < count($date); $i++) {
                  printf("\t  <option value='$i'>%s</option>\n", $date[$i]);
                }
                echo "\t  </select></td></tr>";

                echo "\t  <tr><td>Departure date:</td><td> <SELECT name=\"departure\">\n";
                for($i = 0; $i < count($date); $i++) {
                  printf("\t  <option value='$i'>%s</option>\n", $date[$i]);
                }
                echo "\t  </select></td></tr>\n";
                echo "\t  <tr><td>Travel by:<br></td><td> <SELECT name=\"travelby\">\n";
                for($i = 0; $i < count($xport); $i++) {
                  printf("\t  <option value='$i'>%s</option>\n", $xport[$i]);
                }
                echo "\t  </select></td> </tr>\n";
                echo "\t  <tr><td>Which kind of accomodation:<br></td><td><SELECT name=\"accomodation\">\n";
                for($i = 0; $i < count($acctype); $i++) {
                  printf("\t  <option value='$i'>%s</option>\n", $acctype[$i]);
                }
              ?>
            </select>
          </td>
        </tr>
        <tr><td>Accomodation name (when/once known):<br></td><td><INPUT name="accname"></td></tr>
      </table>
      <INPUT TYPE=SUBMIT NAME=submit VALUE=Register><INPUT TYPE=SUBMIT NAME=submit VALUE=CANCEL></form>
  </body>
</html>
