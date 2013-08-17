<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();
$db = ConnectMysql();


$userid = $_SESSION["userid"];
$userstatus = $_SESSION["userstatus"];
$user = GetEntryFromRequest('user', $userid);
$LBWID = GetEntryFromRequest('LBWID', -1);
$template_details = GetBasicTwigVars($db);

global $date, $xport, $acctype;

if (!($userid == $user || $userstatus == 16)) {
  $template_details['error'] = "You're not allowed edit this user's information";
} else {
  if ($LBWID == -1) {
    $sql = "SELECT * FROM people2 WHERE id='$user'";
    $result = mysql_query($sql, $db);
    if (!$result) {
      error_log(mysql_error($db));
      $template_details['error'] = "Failed to lookup basic user info";
    }
    $row = mysql_fetch_array($result);

    $template_details['LBWID'] = $user;
    $template_details['logon'] = htmlspecialchars($row["logon"]);
    $template_details['firstname'] = htmlspecialchars($row["firstname"]);
    $template_details['surname'] = htmlspecialchars($row["surname"]);
    $template_details['email'] = htmlspecialchars($row["email"]);
    $template_details['city'] = htmlspecialchars($row["city"]);
    $template_details['current_country'] = $row['country'];
    $template_details['countries'] = GetCountries($db);
    $template_details['children'] = $row["children"];
    $template_details['dates'] = $date;
    $template_details['arrival_date'] = $row['arrival'];
    $template_details['departure_date'] = $row['departure'];
    $template_details['travels'] = $xport;
    $template_details['travelby'] = $row['travelby'];
    $template_details['accomodations'] = $acctype;
    $template_details['accomodation_type'] = $row['kindofaccomodation'];
    $template_details['accomodation_name'] = htmlspecialchars($row["nameofaccomodation"]);
    $template_details['attending'] = $row["attending"];
  } else {
    $submit = GetEntryFromRequest('submit', 'CANCEL');
    $logon = GetEntryFromRequest('logon', '');
    $firstname = GetEntryFromRequest('firstname', 'Reto');
    $surname = GetEntryFromRequest('surname', 'Schmidt');
    $email = GetEntryFromRequest('email', 'nobody@local.xxx');
    $city = GetEntryFromRequest('city', 'Accra');
    $country = GetEntryFromRequest('country', 1);
    $attending = intval(GetEntryFromRequest('attending', 0));
    $children = intval(GetEntryFromRequest('children', 0));
    $arrival = GetEntryFromRequest('arrival', 'null');
    $departure = GetEntryFromRequest('departure', 'null');
    $travelby = GetEntryFromRequest('travelby', 'null');
    $kindofaccomodation = GetEntryFromRequest('kindofaccomodation', 'null');
    $nameofaccomodation = GetEntryFromRequest('nameofaccomodation', 'null');
    if ($submit == 'CANCEL') {
      header("Location: userview.php?user=$LBWID");
      exit();
    }
    if (!($LBWID == $userid || $userstatus == 16)) {
      $template_details['error'] = 'Coding error, sorry';
    } else {
      $data_error = Array();
      if (($departure < $arrival) && ($departure != "NULL") && ($arrival != "NULL")) {
        array_push($data_error, "You can not leave before you arrive");
      }
      //more error checking
      $result = mysql_query("SELECT logon FROM people2 WHERE id='$LBWID'", $db);
      $row = mysql_fetch_array($result);
      if ($row["logon"] != $logon) {
        if (strlen($logon) < 4) {
          array_push($data_error, "login must be at least 4 letters");
          $err++;
        }
        $result = mysql_query("SELECT id,logon FROM people2 WHERE (logon LIKE '$logon') AND (id != '$LBWID')", $db);
        if (!$result) {
          error_log(mysql_error($db));
          array_push($data_error, 'Failed to lookup user, something strange going on');
        }
        if (mysql_num_rows($result) > 0) {
          array_push($data_error, "Cannot use logon $logon, as it's already in use");
        }
      }
      if (count($data_error) == 0) {
        $sql = "UPDATE people2 SET logon = '$logon',email = '$email', city='$city', country=$country, attending=$attending, children='$children', arrival=$arrival, departure=$departure, travelby='$travelby', kindofaccomodation='" . $accorder[$kindofaccomodation] . "', nameofaccomodation='$nameofaccomodation', firstname='$firstname', surname='$surname'  WHERE id='$LBWID'";
        $result = mysql_query($sql, $db);
        if (!$result) {
          error_log("SQL failed $sql: " . mysql_error($db));
          $template_details["error"] = 'Failed to update database.';
        } else {
          header("Location: userview.php?user=$LBWID");
          exit();
        }
      } else {
        $template_details['data_errors'] = $data_error;
      }
    }
  }
}

$twig = GetTwig();
/** @noinspection PhpUndefinedMethodInspection */
echo $twig->render('useredit.twig', $template_details);
