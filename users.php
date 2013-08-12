<?php
include("basefunc.inc.php");

/* variables from the environment (GET/POST) */
if (array_key_exists("sort", $_REQUEST)) {
  $sort = $_REQUEST["sort"];
}

$showall = "false";
if (array_key_exists("showall", $_REQUEST)) {
  $showall = $_REQUEST["showall"];
}

CheckLoggedInOrRedirect();

if ($_SESSION["userstatus"] < 8) {
  header("Location: " . getenv("HTTP_REFERER"));
}

header("Pragma: no-cache");
$db = ConnectMysql();

HtmlHead("users", "", $_SESSION["userstatus"], $_SESSION["userid"]);

if (!isset($sort)) $sort = "id";
if (($sort == "id") || ($sort == "logons") || ($sort == "laston")) {
  $sort = "$sort DESC";
}
$where = "where (country.id = country) ";
$result = mysql_query("SELECT people2.id AS id,firstname,surname,email,city,country,status,name,attending,children,arrival,departure,logons,laston FROM people2, country $where ORDER BY " . $sort, $db);
$users = 0;
$link = "";
if (!$result) {
  printf("<br />%s<br />", mysql_error($db));
} else {
  if ($showall == "false") {
    $link = "<a href='?showall=true&sort=$sort'>(Show All)</a>";
    while ($row = mysql_fetch_array($result)) {
      if ($row["logons"] > 0) {
        $users++;
      }
    }
    mysql_data_seek($result, 0);
  } else {
    $users = mysql_num_rows($result);
  }
  print "<br />$users users $link<br />";
}
echo "Page loaded: " . date("H:i:s d-m-y", time()) . "<br />";
?>
<table class='reginfo'>
  <tr>
    <th class='users_id'><a href="?sort=id&showall=<? echo $showall ?>">id</a></th>
    <th class='users_name'><a href="?sort=firstname&showall=<? echo $showall ?>">name</a>,<br/>
      <a href="?sort=surname&showall=<? echo $showall ?>">surname</a></th>
    <?php
    if ($_SESSION["userstatus"] >= 8) {
      ?>
      <th><a href="?sort=email&showall=<? echo $showall ?>">email</a></th>
    <?php
    }
    ?>
    <th><a href="?sort=city&showall=<? echo $showall ?>">city</a>,<br/>
      <a href="?sort=country&showall=<? echo $showall ?>">country</a></th>
    <th><a href="?sort=attending&showall=<? echo $showall ?>">Attending?</a></th>
    <th>children</th>
    <th><a href="?sort=status&showall=<? echo $showall ?>">st</a></th>
    <th>dates</th>
    <th><a href="?sort=logons&showall=<? echo $showall ?>">hits</a></th>
    <th><a href="?sort=laston&showall=<? echo $showall ?>">last</a></th>
  </tr>
  <?php
  global $date;
  while ($row = mysql_fetch_array($result)) {
    $id = $row['id'];
    $firstname = $row['firstname'];
    $surname = $row['surname'];
    $email = $row['email'];
    $city = $row['city'];
    $name = $row['name'];
    $attending = $row['attending'];
    $children = $row['children'];
    $status = $row['status'];
    $arrival = $row['arrival'];
    $departure = $row['departure'];
    $logons = $row['logons'];
    $laston = $row['laston'];
    if ($showall == "true" || $logons > 0) {
      print  "      <tr>\n";
      echo "        <td>$id</td>";
      echo "<td><a href='userview.php?user=$id'>" .
          "$firstname $surname</a></td>";
      if ($_SESSION["userstatus"] >= 8) {
        echo "<td>" . str_replace('@', '@<wbr/>', $email) . "</td>";
      }
      echo "<td>$city, $name</td>";
      printf("<td>%s</td>", $attending ? "Yes" : "No");
      echo "<td>$children</td>";
      echo "<td>$status</td>";
      printf("<td>%s - %s</td>", $date[$arrival], $date[$departure]);
      echo "<td>$logons</td>";
      printf("<td>%s</td>\n", date("Y-m-d H:i:s", $laston));
      print  "      </tr>\n";
    }
  }
  printf("    </table>");

  HtmlTail();
  ?>
