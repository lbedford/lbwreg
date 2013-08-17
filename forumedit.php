<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();

$db = ConnectMysql();
$option = GetEntryFromRequest('option', 'Edit');
$forum = GetEntryFromRequest('forum', 1);

function honchocheck($user, $evt, $db)
{
  $result = mysql_query("SELECT owner FROM Events WHERE id='" . $evt . "'", $db);
  if ($row = mysql_fetch_array($result)) {
    if ($user == $row["owner"] || $_SESSION["userstatus"] >= 16)
      return 1;
  }
  return 0;
}

switch ($option) {
  case "cancel"; // comes from honcho options in forum.
    if (!honchocheck($_SESSION["userid"], $forum, $db)) {
      header("Location: forum.php");
    }
    $template_details = GetBasicTwigVars($db);
    $template_details['forum'] = $forum;

    $twig = GetTwig();
    /** @noinspection PhpUndefinedMethodInspection */
    echo $twig->render('forumedit.twig', $template_details);

    exit();

  case "CONFIRM DELETE EVENT"; //comes from Cancel
    if (!honchocheck($_SESSION["userid"], $event, $db)) {
      break;
    }
    $result = mysql_query("DELETE FROM eventreg WHERE event='$forum'", $db);
    $result = mysql_query("DELETE FROM Events WHERE id='$forum'", $db);
    header("Location: activities.php");
    exit();
}

header("Location: forum.php");