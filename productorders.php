<?php
include("basefunc.inc.php");

CheckLoggedInOrRedirect();

if ($_SESSION["userstatus"] < 8) {
  header("Location: tshirts.php");
}

header("Pragma: no-cache");
$db = connectMysql();

HtmlHead("productorders", "Admin Only", $_SESSION["userstatus"], $_SESSION["userid"]);

echo "";
echo "<h2> T-shirts Order Status </h2>";

$result = mysql_query("SELECT name  FROM tshirts GROUP BY name", $db);
$punters = mysql_num_rows($result);
mysql_free_result($result);

$cols = count($Sizes) + 2;
echo "<table class='reginfo'  >";
echo "<tr><th colspan='$cols'>Aggregated T-shirts Orders by style & size for " . $punters . " buyers<br></th></tr>\n";
echo "<tr><TH>Style</th>";
$quantity = array();
$quantity_size = array();
foreach ($Sizes as $i => $size) {
  echo "<TH>$size</th>";
  $quantity_size[$i] = 0;
}
echo "<TH>Totals</th></tr>\n";
foreach ($Products as $index => $Product) {
  foreach ($Sizes as $i => $size) {
    $quantity[$i] = 0;
  }
  $product_total = 0;
  $result = mysql_query("SELECT sum(quantity) AS number, size FROM tshirts WHERE product='$index' GROUP BY size", $db);
  while ($row = mysql_fetch_array($result)) {
    $quantity[$row['size']] += $row['number'];
    $product_total += $row['number'];
    $quantity_size[$row['size']] += $row['number'];
  }
  mysql_free_result($result);
  echo "<tr><td>" . $Product . "</td> ";
  foreach ($Sizes as $i => $size) {
    echo "<TD align=right>" . $quantity[$i] . "</td>";
  }
  echo "<TD align=right>" . $product_total . "</td></tr>\n";
}
echo "<tr><td>Totals</td>";
$quantity_total_size = 0;
foreach ($Sizes as $i => $size) {
  echo "<TD align=right>" . $quantity_size[$i] . "</td>";
  $quantity_total_size += $quantity_size[$i];
}
echo "<TD>$quantity_total_size</td></tr>\n</table>\n<br>\n";
echo "<br>";

// list all orders by person;
$result = mysql_query("SELECT ref, name, quantity, product, size FROM tshirts ORDER BY ref", $db);
echo "<table class='reginfo'  >";
echo "<tr><TH  COLSPAN=5>Listing of All Orders by  person</th></tr>\n";
echo "<tr ><th>Ref.</th><th>Person</th><th>Number</th><th>Style</th><th>Size</th></tr>";
while ($row = mysql_fetch_array($result)) {
  printf("<tr><td>%d</td><td>%s</td><td>%d </td><td> %s</td><td>%s </td></tr>",
    $row['ref'], $row['name'], $row['quantity'],
    $Products[$row['product']], $Sizes[$row['size']]);
}
echo "</table>";
echo "<br>";

$result = mysql_query("SELECT name, sum(quantity) AS number FROM tshirts GROUP BY name ORDER BY name", $db);
echo "<table class='reginfo'  >";
echo "<tr><TH  COLSPAN=5>Billing info by  person</th></tr>\n";
echo "<tr ><th>Person</th><th>Number</th><th>Euro</th></tr>";
$total = 0;
$euros = 0;
$buyers = 0;
while ($row = mysql_fetch_array($result)) {
  printf("<tr><td>%s</td><td>%d </td><td> %6.2f</td></tr>",
    $row["name"], $row["number"], $row["number"] * getTshirtPrice($Prices, $quantity_total_size));
  $total += $row["number"];
  $euros += $row["number"] * getTshirtPrice($Prices, $quantity_total_size);
  $buyers++;
}
echo "<tr><td colspan=3></td></tr>\n";
printf("<tr><td>%d buyers</td><td>%d </td><td> %8.2f</td></tr>",
  $buyers, $total, $euros);
echo "</table>";
HtmlTail();