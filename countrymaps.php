<?php
require("basefunc.inc.php");

CheckLoggedInOrRedirect();
header("Pragma: no-cache");
$db = ConnectMysql();

$sql = "SELECT sum(people2.attending + people2.children) AS total," .
    "country.code FROM people2,country WHERE " .
    "country.id = people2.country " .
    "AND people2.attending > 0 AND people2.status > 1 " .
    "GROUP BY country.name;";
$result = mysql_query($sql, $db);
$count = mysql_num_rows($result);
//echo "// Data table response\r\n";
echo 'google.visualization.Query.setResponse(' .
    '{"version":"0.6","status":"ok","sig":"1029305520","table": ';
echo "{ cols: [" .
    "{id: 'Country', label: 'Country', type: 'string'}," .
    "{id: 'Attendees', label: 'Attendees', type: 'number'}],";
echo "rows: [";
if ($count) {
  while ($country = mysql_fetch_array($result)) {
    $code = $country['code'];
    $total = $country['total'];
    echo "{c:[{v: '$code'}, {v: $total}]},";
  }
}
echo "]}});";
