<?php
include("basefunc.inc.php");

CheckLoggedInOrRedirect();

$db = connectmysql();

$template_details = GetBasicTwigVars($db);
$sql = "SELECT lbwid,galpix,firstname,surname FROM whois,people2 WHERE (id=lbwid) AND (people2.status>1) AND (people2.attending > 0)  ORDER BY firstname,surname";

$result = mysql_query($sql, $db);
if (!$result) {
  error_log(mysql_error($db));
}

$template_details['pictures'] = array();
while ($row = mysql_fetch_array($result)) {
  array_push($template_details['pictures'], $row);
}


$twig = GetTwig();
/** @noinspection PhpUndefinedMethodInspection */
echo $twig->render('gallery.twig', $template_details);
