<?php # -*- php -*-
require_once '/usr/local/lib/php/vendor/autoload.php';

require("dbconnect.inc.php");

require("config.php");

require_once("lib/template.class.php");

date_default_timezone_set($timeZone);

$date[-1] = "undecided";

# The days of the event
$start_time = strtotime("-1 day", strtotime("$year-$month-$day"));
for ($i = 0; $i <= $duration + 1; $i++) {
  $timestamp = strtotime("+ $i day", $start_time);
  $timestamps[$i] = $timestamp;
  $date[$i] = date("j F", $timestamp);
  $longdate[$i] = date("l j F", $timestamp);
  $shortday[$i] = date("D", $timestamp);
  $weekday[$i] = date("l", $timestamp);
}

function CheckLoggedInOrRedirect()
{
  $_SESSION["userid"] = 0;
  session_start();
  if (!$_SESSION["userid"]) {
    header("Location: login.php");
    exit();
  }
}

function GetTwig()
{
  $loader = new Twig_Loader_Filesystem('templates/');
  return new Twig_Environment($loader, array(
    'cache' => '/var/tmp/compilation_cache',
    'debug' => true,
  ));
}

function GetBasicTwigVars($db)
{
  global $year, $location, $date;
  $template_details = Array();
  if ($_SESSION["userstatus"] > 8) {
    $template_details['admin'] = 1;
  }

  $template_details['year'] = $year;
  $template_details['location'] = $location;
  $template_details['start_date'] = $date[1];
  $template_details['end_date'] = $date[count($date) - 3];
  $template_details['logged_in_name'] = GetLbwUserName($_SESSION['userid'], $db);
  return $template_details;
}

function GetNumberOfMessagesInEvent($event, $db)
{
  $sql = "SELECT discussions.id as mid, firstname, surname, subject, posted " .
      "FROM discussions, people2 WHERE (people2.id=writer)  AND " .
      "(forum = '$event') ORDER BY posted";
  $result = mysql_query($sql, $db);
  $template_details['messages'] = Array();
  if (!$result) {
    error_log("$sql failed: " . mysql_error($db));
    return 0;
  } else {
    return mysql_num_rows($result);
  }
}

function HtmlHead($page, $title, $status, /** @noinspection PhpUnusedParameterInspection */
                  $userid, $javascript = '')
{
  echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
  echo "<html>\n";
  echo "<head>\n";
  include("html/head.html");
  echo "  <link rel='stylesheet' href='reg.css' type='text/css' />\n";
  if (!empty($javascript)) {
    echo "$javascript";
  }
  echo "</head>\n";
  echo "<body class='protected'>\n";
  echo "  <div class='content' >\n";
  echo "    <table border='0'>\n";
  include("html/header.html");
  echo "      <tr>\n";
  echo "        <td class='leftcolumn strut'>\n";
  echo "          <br />\n";
  echo "          " . htmlspecialchars($title);
  echo "        </td>\n";
  echo "        <td class='centercolumn'>\n";

  echo "          <div class='subnavbar'  style=\"text-align: center;\">\n";
  global $menu, $admin_menu;
  Template::menu($menu, $page, $status);
  echo "          </div>\n";

  if ($status >= 8) {
    echo "          <div class='adminbar' >\n";
    Template::menu($admin_menu, $page, $status);
    echo "</div>\n";
  }
  echo "<br />";

  return 1;
}


function HtmlTail()
{
  global $teammail;
  ?>
  <table class='footer'>
    <tr>
      <td class='leftnote'></td>
      <td class='rightnote'> &nbsp;&nbsp; </td>
    </tr>
    <tr>
      <td class="leftnote">
      </td>
      <td class="rightnote">
        <script type="text/javascript" language="JavaScript1.1"><!--
		showAddr("", "<?php echo $teammail ?>", "The Organising Team");;
		//--></script>
        &nbsp;&nbsp;
      </td>
    </tr>
  </table>
  </td></tr></table>
  </div>
  <!-- Piwik -->
  <script type="text/javascript">var _paq = _paq || [];
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function () {
      var u = (("https:" == document.location.protocol) ? "https" : "http") + "://lbwreg.draiocht.net/analytics//";
      _paq.push(['setTrackerUrl', u + 'piwik.php']);
      _paq.push(['setSiteId', 1]);
      var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
      g.type = 'text/javascript';
      g.defer = true;
      g.async = true;
      g.src = u + 'piwik.js';
      s.parentNode.insertBefore(g, s);
    })();</script>
  <noscript><p><img src="http://lbwreg.draiocht.net/analytics/piwik.php?idsite=1" style="border:0" alt=""/></p>
  </noscript>
  <!-- End Piwik Code -->
  </body>
  </html>

<?php
}

function GetCountry($country, $db)
{
  $resp = mysql_query("SELECT name FROM country WHERE id LIKE '$country'", $db);
  $myrow = mysql_fetch_array($resp);
  return $myrow["name"];
}

function GetLbwUserDetails($geekid, $db)
{
  if ($geekid < 0) {
    return Array("John", "Doe", "nobody@nowhere.com");
  } else {
    $query = "SELECT firstname, surname, email from people2 " .
        "where id='$geekid'";
    $result = mysql_query($query, $db);
    $details = mysql_fetch_array($result);
    return $details;
  }
}

function GetLbwUserName($geekid, $db)
{
  $details = GetLbwUserDetails($geekid, $db);
  return $details['firstname'] . ' ' . $details['surname'];
}

function Event2sched($type, $day, $hour, $duration)
{
  global $timestamps;
  if ($type == 1)
    $sched = "All Week";
  else {
    $evday = $day;
    if ($evday < 1)
      $sched = "Not yet scheduled";
    else {
      $starttimestamp = $timestamps[$evday] + ($hour * 3600);
      $endtimestamp = $starttimestamp + ($duration * 3600);
      $endformat = "H:i";
      if (date("l", $starttimestamp) != date("l", $endtimestamp)) {
        $endformat = "l " . $endformat;
      }
      $sched = date("l j F H:i", $starttimestamp) . " - " . date($endformat, $endtimestamp);
    }
  }
  return $sched;
}

function text2html($text)
{

  $text = preg_replace("/^/", "<p>", $text);

  $text = preg_replace("/\n\r?(\n\r?)+/", "\n</p>\n<p>\n", $text);
  $text = preg_replace("/[^='\"](https?:\/\/[^ \t\n\r>]+)/", " <a href='$1'>$1</a><br />", $text);

  $text = preg_replace("/$/", "</p>", $text);

  return $text;
}

function getTshirtPrice($Prices, $quantity)
{
  $p = 0;
  foreach ($Prices as $threshold => $Price) {
    if (intval($quantity) >= $threshold) {
      $p = $Price;
    }
  }

  return $p;
}

function getForumOwnerEmail($forum)
{
  global $db;
  $query = "select people2.email from people2, Events where " .
      "Events.id = $forum and people2.id = Events.owner";
  $resp = mysql_query($query, $db);
  if (!$resp) {
    printf("<br />$query: %s<br />", mysql_error($db));
  }
  $myrow = mysql_fetch_array($resp);
  return $myrow["email"];
}

function getForumOwnerName($forum)
{
  global $db;
  $query = "select people2.firstname, people2.surname from people2, Events where " .
      "Events.id = $forum and people2.id = Events.owner";
  $resp = mysql_query($query, $db);
  if (!$resp) {
    printf("<br />$query: %s<br />", mysql_error($db));
  }
  $myrow = mysql_fetch_array($resp);
  return $myrow["firstname"] . " " . $myrow["surname"];
}

function getNameOfEvent($event)
{
  global $db;
  $query = "SELECT name FROM Events WHERE id = '$event'";
  $resp = mysql_query($query, $db);
  if (!$resp) {
    printf("<br />$query: %s<br />", mysql_error($db));
  }
  $myrow = mysql_fetch_array($resp);
  return $myrow["name"];
}

;

function getListOfUsersForEvent($event)
{
  global $db;
  $query = "select concat(people2.firstname, ' ', people2.surname) from " .
      "people2, eventreg where eventreg.geek = people2.id " .
      "and event = $event";
  $resp = mysql_query($query, $db);
  if (!$resp) {
    printf("<br />$query: %s<br />", mysql_error($db));
  }
  $users = array();
  while ($myrow = mysql_fetch_row($resp)) {
    array_push($users, $myrow[0]);
  };
  return implode($users, ",");
}

;

function getDescriptionOfEvent($event)
{
  global $db;
  $query = "SELECT description FROM Events WHERE id = '$event'";
  $resp = mysql_query($query, $db);
  if (!$resp) {
    return "";
  }
  $myrow = mysql_fetch_array($resp);
  return $myrow["description"];
}

function GetEntryFromRequest($entry, $default)
{
  if (array_key_exists($entry, $_REQUEST)) {
    return mysql_real_escape_string(trim($_REQUEST[$entry]));
  }
  return $default;
}

;
?>
