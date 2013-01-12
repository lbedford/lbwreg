<?php
     
    include("basefunc.inc.php");
 
    /* variables from environment (GET/POST) */
     
    $_SESSION["userid"] = 0;
    session_start();
    if (! $_SESSION["userid"] ) {
        header("Location: login.php"); exit();
    }
     
    // extract session first so the request can't override it.
    extract($_SESSION);
    extract($_REQUEST, EXTR_SKIP);

    $db = connectMysql();
     
    if (isset($size)) {
      $size = mysql_real_escape_string(trim($size));
    }
    if (isset($product)) {
      $product = mysql_real_escape_string(trim($product));
    }
    if (isset($record)) {
      $record = mysql_real_escape_string(trim($record));
    }
    if (isset($number)) {
      $number = mysql_real_escape_string(trim($number));
    }

    $ProductCount = count($Products);
    if (isset($option)) {
	switch($option) {
	    case "Order":
	    $quantity = intval($number);
	    $result = mysql_query("SELECT * from tshirts where (person='".$_SESSION["userid"]."') AND (size='$size') AND (product='$product') ", $db);
	    if (!$result) {
		echo mysql_error($db);
	    }
	    if (!mysql_num_rows($result)) {
		if ($quantity > 0)
		    if (!mysql_query("INSERT INTO tshirts (person,name,product,quantity,size) VALUES ('".$_SESSION["userid"]."','".$_SESSION["username"]."','$product','$quantity','$size')", $db))
		    	echo mysql_error($db)."<br>";
	    }
	    break;

	    case "change":
	    $result = mysql_query("SELECT person, product, size, quantity FROM tshirts WHERE (ref='$record')", $db);
	    $row = mysql_fetch_array($result);
            extract($row);
	    if ($_SESSION["userid"] != $person)
		header("Location: t-shirts.php");

	    echo "<FORM METHOD=POST><INPUT TYPE=HIDDEN NAME=record VALUE=$record>";
	    echo "<INPUT TYPE=HIDDEN NAME=product VALUE=$product>";
	    echo "How Many ".$Products[$product].", ".$Sizes[$size]." Tshirts do you want ?<INPUT TYPE=TEXT NAME=number VALUE=$quantity>";
	    echo "<INPUT TYPE=SUBMIT NAME=option VALUE=Update></form>";
	    echo "<br><hr>";
	    echo "</body></html>\n\n";
	    exit();

	    case "Update":
	    $quantity = intval($number);
	    if ($quantity == 0) {
		mysql_query("DELETE FROM tshirts WHERE (ref='$record') AND (person='".$_SESSION["userid"]."')", $db);
		break;
	    }
	    mysql_query("UPDATE tshirts SET quantity=$quantity WHERE (ref='$record') AND (person='".$_SESSION["userid"]."')", $db);
	    break;
	}
    }
    $pagestat = 0;
     
    HtmlHead("tshirts", "T-Shirt Order Page", $_SESSION["userstatus"], $_SESSION["userid"]);
     
     
    if (($_SESSION["userid"] == 9) || ($_SESSION["userstatus"] > 8)) {
        echo "<br><table class='reginfo' ><tr><th><A href=productorders.php>Order Status</a></th></tr></table><br>";
    }
     

    $now = time();
    # mktime(hour, minute, second, month, day, year, isDst)
    $open  = mktime(00, 00, 00, 6, 10, 2011, 1);
    $close = mktime(00, 00, 00, 8, 6, 2012, 1);
    if ($now < $open) {
        echo "<h2>Orders not open yet! Sorry!</h2>";
    } else if ( $open <= $now && $now < $close) {
	$ttg = $close - $now;	
        $d = (int)($ttg/86400);
        $h = (int)(($ttg%86400)/3600);
        $m = (int)(($ttg%3600)/60);
        $daysuffix = "";
        if ($d > 1) {
          $daysuffix = "s";
        }
        if ($d > 0)
        {
          echo "<h2>".$d." day".$daysuffix.", ".$h." hrs, ".$m." minutes left to order </h2>";
        }
        else 
        {
          echo "<h2><span color=red>Only</span> ".$h." hrs ".$m." minutes left to order </h2>";
        }
        $pagestat = 1;
    } else {
	echo "<h2>Orders closed! Sorry!</h2>";
    }

    echo "<table class='reginfo' cellpadding='0'>\n";
    $colspan = $ProductCount + 1;
    $sql = "SELECT SUM(quantity) from tshirts";
    $result = mysql_query($sql, $db);
    $row = mysql_fetch_array($result);
    $Price = getTshirtPrice($Prices, $row[0]);

    echo "<tr ><th colspan='$colspan'>Business Proposal: Quality LBW T-shirts!!!11!!11</th></tr>";
    echo "<tr ><th colspan='$colspan'><center>T-shirt price: &euro;$Price<br>";
    #echo "The underpants fund will be raided to subsidise the orders as necessary.";
    #echo "<table>\n<tr><th>Threshold</th><th>Price for all orders</th></tr>\n";
    #foreach($Prices as $threshold => $CurrentPrice) {
      #echo "<tr><td>$threshold</td><td>&euro;$CurrentPrice</td></tr>\n";
    #}
    #echo "</table>\n";
    echo "</center></th></tr>\n<tr>";
    foreach($Products as $Product) {
      echo "<th>".$Product."</th>";
    }
    echo "</tr>\n<tr>";
    foreach($Products as $Product) {
      echo "<th><A Href=pix/".$Product."-front.jpg><img src=pix/tnail/".$Product."-front.jpg ></td>";
    }
    echo "</tr><tr>";
    echo "<td colspan=2><center>Click on the image for large view of the artwork.<br>Back of shirts will list the places and dates of all LBWs.<br>";
    //echo "<a href='http://www.promodoro-shop.de/frontend_1/files/pdf/D_3000.pdf'>Men's</a>/<a href='http://pomodoro.de/frontend_1/files/pdf/D_3005.pdf'>Women's</a> sizing information<br>";
    echo "<h3>Important: </h3><p>T-shirts will not be shipped. They must be picked up in person at the LBW. If your plans change, please organise to have your order collected, or cancel it using the link below.</p>";
    echo "</center></td>";
    echo "</tr>";
    echo "<tr><th colspan=$colspan></th></tr>\n";
    $result = mysql_query("select * from tshirts where person='".$_SESSION["userid"]."'", $db);
    if (!$result) {
        echo mysql_error($db);
    }
    if (mysql_num_rows($result) > 0) {
        echo "<tr ><TH colspan=$ProductCount>Your current order</th></tr>";
        echo "<tr ><TD Colspan=$ProductCount><b>";
        echo "<table class='reginfo' width=100% >";
        echo "<tr><TH width=10%>Number</th><TH width=10%>Colour</th><TH width=20%>Size</th><TH width=20%>Total Price per Order</TH><TH width=30%>&nbsp;</th></tr>";
        while ($row = mysql_fetch_array($result)) {
            extract($row);
            if ($pagestat)
                printf("<tr><td>%d</td><td><center>%s</center></td><td><center>%s</center></td><td><center>&euro;%s</center></td><td><a href=t-shirts.php?option=change&record=%s>change order</a></td></tr>\n", $quantity, $Products[$product], $Sizes[$size], $quantity * $Price, $ref);
            else
                printf("<tr><td>%d</td><td> %s</td><td> %s </td><td>%s</td></tr>\n", $quantity, $Products[$product], $Sizes[$size], $quantity * $Price);
        }
        echo "</b></table></td></tr>\n";
    } else {
        echo "<tr><TH COLSPAN=4> <br>You have not ordered any of our fine T-shirts yet<br>&nbsp;</th></tr>\n";
    }
    if ($pagestat > 0) {
        echo "<tr ><td colspan=$ProductCount align=center valign=middle><FORM METHOD=POST>";
         
        echo "<b>Add <INPUT TYPE=TEXT NAME=number value=1 SIZE=4 MAXLEN=4> Size <SELECT name=size></b>";
        foreach($Sizes as $i => $size)
          printf ("<option value='$i' %s> $size </option>\n", ($i == 7)?"selected":"");
        echo "</select>";
        echo "<b> Colour <SELECT name=product></b>";
        foreach ($Products as $i => $colour) {
          if ($colour) 
            printf("<option value=$i %s>$colour</option>\n", ($i == 0)?"selected":"");
        }
        echo "</select>";
        echo " <INPUT TYPE=SUBMIT NAME=option value=Order> ";
        echo "</form></td></tr>\n";
    }
    else {
        echo "<tr  ><TH COLSPAN=$ProductCount>&nbsp;<th></tr>\n";
    }
    echo "</table>";
    #echo "<h3>Important: </h3><p>T-shirts will not be shipped. They must be picked up in person at the LBW. If your plans change, please organise to have your order collected, or cancel it using the link above.</p>";
    HtmlTail();
?>

