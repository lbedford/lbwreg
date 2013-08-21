<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();
$db = ConnectMysql();

$order = GetEntryFromRequest('order', '');
if ($order == "") {
  $order = "surname";
}

$ordering = "ASC";
if ($order == "present") {
  $ordering = "DESC";
}
$template_details = GetBasicTwigVars($db);
$showall = GetEntryFromRequest('showall', 0);
if (!$template_details['admin']) {
  $showall = 0;
}

$query = "SELECT people2.id as id, firstname, surname, email, " .
    "city, country.name as country, status, arrival, departure, attending, " .
    "children, kindofaccomodation, present FROM people2,country " .
    "where (country.id = people2.country) ORDER by $order $ordering";
$result = mysql_query($query, $db);
if (!$result) {
  error_log(mysql_error($db));
  $template_details['error'] = 'Error looking up participants';
} else {
  $r2 = mysql_query("SELECT Count(*) AS count,sum(attending) AS geeks,sum(children) AS kids FROM people2 WHERE (attending>0) AND (status>1)", $db);
  $totals = mysql_fetch_array($r2);
  $template_details['registered_adults'] = $totals["geeks"];
  $template_details['registered_children'] = $totals["kids"];
  $template_details['participants'] = array();
  global $date, $acctype;
  while ($row = mysql_fetch_array($result)) {
    if (is_null($row['arrival'])) {
      $row['arrival'] = -1;
    }
    if (is_null($row['departure'])) {
      $row['departure'] = -1;
    }
    $row["arrival"] = $date[$row["arrival"]];
    $row["departure"] = $date[$row["departure"]];
    $row["kindofaccomodation"] = $acctype[$row["kindofaccomodation"]];
    $row['present'] = $row['present'] ? "Yes" : "No";
    if (($showall) || (($row['status'] > 0) && ($row['attending']))) {
      array_push($template_details['participants'], $row);
    }
  }

  $sql = "SELECT country AS name, sum(1) AS adults,sum(children) AS children, name FROM people2, country WHERE (country.id = people2.country) AND (attending>0) AND (status>=2)   GROUP BY country ORDER BY adults DESC";
  $result = mysql_query($sql, $db);

  $template_details['countries'] = array();
  if (!$result) {
    error_log(mysql_error($db));
    $template_details['error'] = 'Error looking up countries';
  } else {
    while ($row = mysql_fetch_array($result)) {
      array_push($template_details['countries'], $row);
    }
  }
}

$twig = GetTwig();
/** @noinspection PhpUndefinedMethodInspection */
echo $twig->render('participants.twig', $template_details);
