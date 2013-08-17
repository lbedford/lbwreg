<?php
include("basefunc.inc.php");

CheckLoggedInOrRedirect();

$userstatus = $_SESSION['userstatus'];
$userid = $_SESSION['userid'];
$db = connectMysql();

switch ($_REQUEST['option']) {
  case "edit":
    $ride = mysql_real_escape_string(trim($_REQUEST['ride']));
    $row = mysql_fetch_array(mysql_query("SELECT person, type, email, dest, orig, date, space, notes FROM rides WHERE id='$ride'", $db));
    if ($row["person"] != $userid) {
      echo "You can not edit this entry<br>";
      HtmlTail();
      exit();
    }

    HtmlHead("rideforum", "Ride Forum", $userstatus, $userid);
    echo "<FORM METHOD=POST>";
    echo "<INPUT TYPE=HIDDEN NAME=xtype VALUE=" . $row["type"] . ">";
    echo "<INPUT TYPE=HIDDEN NAME=person VALUE=$userid>";
    echo "<table class='reginfo'>";
    printf("<tr><TD COLSPAN=2>A Ride being %s by %s</td></tr>", ($row["type"] == "offer") ? "Offered" : "requested", GetLbwUserName($userid, $db));
    echo "<tr><td>Email:</td><td><INPUT TYPE=TEXT NAME=email VALUE=" . $row["email"] . " size='60'></td></tr>";

    if (!strcmp($row["dest"], $location)) {
      echo "<tr><td>From:</td><td><INPUT TYPE=TEXT NAME=\"orig\" size='60' value=\"" . $row["orig"] . "\"></td></tr>";
      echo "<INPUT TYPE=HIDDEN NAME=\"dest\" VALUE=\"$location\">";
      echo "<tr><td>  To:</td><td> $location</td></tr>";
    } else {
      echo "<tr><td>  From:</td><td> $location</td></tr>";
      echo "<INPUT TYPE=HIDDEN NAME=\"orig\" VALUE=\"$location\">";
      echo "<tr><td>To:</td><td><INPUT TYPE=TEXT NAME=\"dest\" size='60' VALUE=\"" . $row["dest"] . "\"></td></tr>";
    }
    echo "<tr><td>Date</td><td><INPUT TYPE=TEXT SIZE=24 NAME=\"xdate\" VALUE=\"" . $row["date"] . "\"></td></tr>";
    echo "<tr><td>Places</td><td><INPUT TYPE=TEXT SIZE=32 NAME=\"space\" VALUE=\"" . $row["space"] . "\"></td></tr>";
    echo "<tr><td>Notes</td><td><TEXTAREA NAME=notes cols=60 rows=4 MAXLEN=1024 >" . $row["notes"] . "</textarea></td></tr>";
    echo "<tr><TD COLSPAN=2><INPUT TYPE=SUBMIT NAME=option VALUE=update><INPUT TYPE=SUBMIT NAME=option VALUE=delete><INPUT TYPE=SUBMIT NAME=option VALUE=cancel></td></tr>";
    echo "</table></form>";
    HtmlTail();
    exit();

// TODO(lbedford): escape this stuff too
  case "save":
    $xtype = GetEntryFromRequest('xtype', 'null');
    $orig = GetEntryFromRequest('orig', 'null');
    $dest = GetEntryFromRequest('dest', 'null');
    $xdate = GetEntryFromRequest('xdate', 'null');
    $email = GetEntryFromRequest('email', 'null');
    $space = GetEntryFromRequest('space', 'null');
    $notes = GetEntryFromRequest('notes', 'null');
    $sql = "INSERT INTO rides (type,orig,dest,date,person,email,space,notes) VALUES('$xtype','$orig','$dest','$xdate','$userid','$email','$space','$notes')";
    if (!($result = mysql_query($sql, $db))) {
      echo mysql_error($db);
    }
    break;
  case "cancel":
    header("Location: rides.php");
    exit();

  case "update":
    $orig = GetEntryFromRequest('orig', 'null');
    $dest = GetEntryFromRequest('dest', 'null');
    $xdate = GetEntryFromRequest('xdate', 'null');
    $space = GetEntryFromRequest('space', 'null');
    $notes = GetEntryFromRequest('notes', 'null');
    mysql_query("UPDATE rides SET orig='$orig',dest='$dest',date='$xdate',space='$space',notes='$notes' WHERE id='$ride'", $db);
    header("Location: rides.php");
    exit();

  case "delete":
    $ride = mysql_real_escape_string(trim($ride));
    $sql = "DELETE FROM rides WHERE id='$ride'";
    mysql_query($sql, $db);
    header("Location: rides.php");
    exit();

  case "Offer a Ride":
  case "Request a Ride":
    $dir = $_REQUEST['dir'];
    if (!strncasecmp("Offer", $option, 5))
      $xtype = "offer";
    if (!strncasecmp("Reque", $option, 5))
      $xtype = "request";
    $row = mysql_fetch_array(mysql_query("SELECT firstname,surname,email FROM people2 WHERE id='$userid'", $db));

    HtmlHead("rideforum", "Ride Forum", $userstatus, $userid);
    echo "<FORM METHOD=POST>";
    echo "<INPUT TYPE=HIDDEN NAME=xtype VALUE=$xtype>";
    echo "<INPUT TYPE=HIDDEN NAME=person VALUE=$userid>";
    echo "<table class='reginfo'>";
    printf("<tr><TD COLSPAN=2>A Ride being %s by %s</td></tr>", ($xtype == "offer") ? "offered" : "requested", GetLbwUserName($userid, $db));
    echo "<tr><td>Email:</td><td><INPUT TYPE=TEXT NAME=email VALUE=" . $row["email"] . " size='60'></td></tr>";

    if (!strcmp($dir, "TO")) {
      echo "<tr><td>From:</td><td><INPUT TYPE=TEXT NAME=\"orig\" size='60'></td></tr>";
      echo "<INPUT TYPE=HIDDEN NAME=\"dest\" VALUE=\"$location\">";
      echo "<tr><td>  To:</td><td>$location</td></tr>";
    } else {
      echo "<tr><td>  From:</td><td>$location</td></tr>";
      echo "<INPUT TYPE=HIDDEN NAME=\"orig\" VALUE=\"$location\">";
      echo "<tr><td>To:</td><td><INPUT TYPE=TEXT NAME=\"dest\" size='60'></td></tr>";
    }
    echo "<tr><td>Date</td><td><INPUT TYPE=TEXT SIZE=24 NAME=\"xdate\"></td></tr>";
    echo "<tr><td>Places</td><td><INPUT TYPE=TEXT SIZE=32 NAME=\"space\"></td></tr>";
    echo "<tr><td>Notes</td><td><TEXTAREA NAME=notes cols=60 rows=4></textarea></td></tr>";
    echo "<tr><TD COLSPAN=2><INPUT TYPE=SUBMIT NAME=option VALUE=save><INPUT TYPE=SUBMIT NAME=option VALUE=cancel></td></tr>";
    echo "</table></form>";
    HtmlTail();
    exit();
}