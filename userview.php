<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();

$userstatus = $_SESSION["userstatus"];
$userid = $_SESSION["userid"];
$user = mysql_real_escape_string(trim($_REQUEST["user"]));
global $date, $xport, $acctype;
$db = ConnectMysql();

$template_details = GetBasicTwigVars($db);
$sql = "SELECT firstname, surname, city, country.name as name, " .
    "arrival, departure, attending, children, travelby, status, " .
    "kindofaccomodation, nameofaccomodation, logon, email FROM " .
    "people2,country WHERE (country.id=people2.country) AND " .
    "(people2.id='$user')";
if (!$result = mysql_query($sql, $db)) {
  error_log(mysql_error($db));
  $template_details['error'] = 'Sorry, user not found';
} else {
  $row = mysql_fetch_array($result);

  $template_details['user'] = $user;
  $template_details['firstname'] = $row["firstname"];
  $template_details['surname'] = $row["surname"];
  $template_details['city'] = $row["city"];
  $template_details['country'] = $row["name"];
  $template_details['arrival_date'] = $date[$row["arrival"]];
  $template_details['departure_date'] = $date[$row["departure"]];
  $template_details['attending'] = $row["attending"] ? "Yes" : "No";
  $template_details['children'] = $row["children"];
  $template_details['travelling_by'] = $xport[$row["travelby"]];
  $template_details['status'] = $row["status"];
  $template_details['accomodation_type'] = $acctype[$row["kindofaccomodation"]];
  $template_details['accomodation_name'] = $row["nameofaccomodation"];
  $template_details['username'] = $row["logon"];
  $template_details['email'] = $row["email"];

  $template_details['picture_url'] = 0;

  $sql = "SELECT whois,galpix FROM people2,whois WHERE (people2.id='$user') " .
      "AND (people2.id=whois.lbwid)";
  if (!$result = mysql_query($sql, $db)) {
    error_log(mysql_error($db));
    $template_details['error'] = 'Error looking up photo';
  } else {
    $pix = mysql_fetch_array($result);
    $template_details['picture_url'] = 'pictures/' . $pix['galpix'];
  }
}
$twig = GetTwig();
/** @noinspection PhpUndefinedMethodInspection */
echo $twig->render('userview.twig', $template_details);
