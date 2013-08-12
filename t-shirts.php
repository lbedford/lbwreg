<?php

include("basefunc.inc.php");

/* variables from environment (GET/POST) */

CheckLoggedInOrRedirect();

$db = connectMysql();


$ProductCount = count($Products);
if (array_key_exists('option', $_REQUEST)) {
  switch ($_REQUEST['option']) {
    case "Order":
      # needs $number, $product, $size
      $size = -1;
      if (array_key_exists('size', $_REQUEST)) {
        $size = mysql_real_escape_string(trim($_REQUEST['size']));
      }
      $product = -1;
      if (array_key_exists('product', $_REQUEST)) {
        $product = mysql_real_escape_string(trim($_REQUEST['product']));
      }


      $result = mysql_query("SELECT * from tshirts where (person='" . $_SESSION["userid"] . "') AND (size='$size') AND (product='$product') ", $db);
      if (!$result) {
        echo mysql_error($db);
      }
      if (!mysql_num_rows($result)) {
        $number = 0;
        if (array_key_exists('number', $_REQUEST)) {
          $number = mysql_real_escape_string(trim($_REQUEST['number']));
        }
        $quantity = intval($number);
        if ($quantity > 0)
          if (!mysql_query("INSERT INTO tshirts (person,name,product,quantity,size) VALUES ('" . $_SESSION["userid"] . "','" . $_SESSION["username"] . "','$product','$quantity','$size')", $db))
            echo mysql_error($db) . "<br>";
      }
      break;

    case "change":
      $record = -1;
      if (array_key_exists('record', $_REQUEST)) {
        $record = mysql_real_escape_string(trim($_REQUEST['record']));
      }
      $result = mysql_query("SELECT person, product, size, quantity FROM tshirts WHERE (ref='$record')", $db);
      $row = mysql_fetch_array($result);
      $person = $row['person'];
      $product = $row['product'];
      $size = $row['size'];
      $quantity = $row['quantity'];
      if ($_SESSION["userid"] != $person)
        header("Location: t-shirts.php");

      echo "<FORM METHOD=POST><INPUT TYPE=HIDDEN NAME=record VALUE=$record>";
      echo "<INPUT TYPE=HIDDEN NAME=product VALUE=$product>";
      echo "How Many " . $Products[$product] . ", " . $Sizes[$size] . " Tshirts do you want ?<INPUT TYPE=TEXT NAME=number VALUE=$quantity>";
      echo "<INPUT TYPE=SUBMIT NAME=option VALUE=Update></form>";
      echo "<br><hr>";
      echo "</body></html>\n\n";
      exit();

    case "Update":
      $number = 0;
      if (array_key_exists('number', $_REQUEST)) {
        $number = mysql_real_escape_string(trim($_REQUEST['number']));
      }
      $quantity = intval($number);
      $record = -1;
      if (array_key_exists('record', $_REQUEST)) {
        $record = mysql_real_escape_string(trim($_REQUEST['record']));
      }
      if ($quantity == 0) {
        mysql_query("DELETE FROM tshirts WHERE (ref='$record') AND (person='" . $_SESSION["userid"] . "')", $db);
        break;
      }
      mysql_query("UPDATE tshirts SET quantity=$quantity WHERE (ref='$record') AND (person='" . $_SESSION["userid"] . "')", $db);
      break;
  }
}
$pagestat = 0;

HtmlHead("tshirts", "T-Shirt Order Page", $_SESSION["userstatus"], $_SESSION["userid"]);


if ($_SESSION["userstatus"] > 8) {
  echo "<br/>";
  echo "<table class='reginfo' >";
  echo "<tr>";
  echo "<th>";
  echo "<A href=productorders.php>Order Status</a>";
  echo "</th>";
  echo "</tr>";
  echo "</table>";
  echo "<br/>";
}


$now = time();
# mktime(hour, minute, second, month, day, year, isDst)
$open = mktime(00, 00, 00, 6, 10, 2011);
$close = mktime(00, 00, 00, 8, 6, 2017);
if ($now < $open) {
  echo "<h2>Orders not open yet! Sorry!</h2>";
} else if ($open <= $now && $now < $close) {
  $ttg = $close - $now;
  $d = (int)($ttg / 86400);
  $h = (int)(($ttg % 86400) / 3600);
  $m = (int)(($ttg % 3600) / 60);
  $daysuffix = "";
  if ($d > 1) {
    $daysuffix = "s";
  }
  if ($d > 0) {
    echo "<h2>" . $d . " day" . $daysuffix . ", " . $h . " hrs, " . $m . " minutes left to order </h2>";
  } else {
    echo "<h2><span color=red>Only</span> " . $h . " hrs " . $m . " minutes left to order </h2>";
  }
  $pagestat = 1;
} else {
  echo "<h2>Orders closed! Sorry!</h2>";
}

echo "<table class='reginfo'>\n";
$colspan = $ProductCount + 1;
$sql = "SELECT SUM(quantity) FROM tshirts";
$result = mysql_query($sql, $db);
$row = mysql_fetch_array($result);
$Price = getTshirtPrice($Prices, $row[0]);

echo "<tr ><th colspan='$colspan'>Business Proposal: Quality LBW T-shirts!!!11!!11</th></tr>";
echo "<tr ><th colspan='$colspan' style=\"text-align: center;\">T-shirt price: &pound;$Price<br>";
echo "</th></tr>\n<tr>";
foreach ($Products as $Product) {
  echo "<th>" . $Product . "</th>";
}
echo "</tr>\n<tr>";
foreach ($Products as $Product) {
  echo "<th><A Href=pix/" . $Product . "-front.jpg><img src=pix/tnail/" . $Product . "-front.jpg ></td>";
}
echo "</tr><tr>";
echo "<td colspan=2  style=\"text-align: center;\"><center>Click on the " .
    "image for large view of the artwork.<br>" .
    "Back of shirts will list the places and dates of all LBWs.<br>";
echo "<h3>Important: </h3><p>T-shirts will not be shipped. " .
    "They must be picked up in person at the LBW. " .
    "If your plans change, please organise to have your order " .
    "collected, or cancel it using the link below.</p>";
echo "</td>";
echo "</tr>";
echo "<tr><th colspan=$colspan></th></tr>\n";
$result = mysql_query("select ref, quantity, product, size from tshirts where person='" . $_SESSION["userid"] . "'", $db);
if (!$result) {
  echo mysql_error($db);
}
if (mysql_num_rows($result) > 0) {
  echo "<tr ><TH colspan=$ProductCount>Your current order</th></tr>";
  echo "<tr ><TD Colspan=$ProductCount><b>";
  echo "<table class='reginfo'>";
  echo "<tr><TH>Number</th><TH>Style</th><TH>Size</th><TH>Total Price per Order</TH><TH>&nbsp;</th></tr>";
  while ($row = mysql_fetch_array($result)) {
    $quantity = $row['quantity'];
    $product = $row['product'];
    $size = $row['size'];
    $ref = $row['ref'];
    if ($pagestat)
      printf("<tr><td>%d</td><td style=\"text-align: center;\">%s</td>" .
        "<td style=\"text-align: center;\">%s</td>" .
        "<td style=\"text-align: center;\">&euro;%s</td>" .
        "<td><a href=t-shirts.php?option=change&record=%s>change order" .
        "</a></td></tr>\n", $quantity, $Products[$product],
        $Sizes[$size], $quantity * $Price, $ref);
    else
      printf("<tr><td>%d</td><td> %s</td><td> %s </td><td>%s</td></tr>\n", $quantity, $Products[$product], $Sizes[$size], $quantity * $Price);
  }
  echo "</b></table></td></tr>\n";
} else {
  echo "<tr><TH COLSPAN=4> <br>You have not ordered any of our fine T-shirts yet<br>&nbsp;</th></tr>\n";
}
if ($pagestat > 0) {
  echo "<tr ><td colspan=$ProductCount>";
  echo "<FORM METHOD=POST>";

  echo "<b>Add <INPUT TYPE=TEXT NAME=number value=1 SIZE=4> Size <SELECT name=size></b>";
  foreach ($Sizes as $i => $size)
    printf("<option value='$i' %s> $size </option>\n", ($i == 7) ? "selected" : "");
  echo "</select>";
  echo "<b> Style <SELECT name=product></b>";
  foreach ($Products as $i => $style) {
    if ($style)
      printf("<option value=$i %s>$style</option>\n", ($i == 0) ? "selected" : "");
  }
  echo "</select>";
  echo " <INPUT TYPE=SUBMIT NAME=option value=Order> ";
  echo "</form></td></tr>\n";
} else {
  echo "<tr  ><TH COLSPAN=$ProductCount>&nbsp;<th></tr>\n";
}
echo "</table>";
HtmlTail();