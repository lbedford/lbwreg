<?php
require("basefunc.inc.php");

$db = ConnectMysql();
session_start();
if (!array_key_exists("userid", $_SESSION)) {
  header("Location: login.php");
  exit();
}

$action = $_REQUEST['action'];

if (array_key_exists('lbwid', $_REQUEST)) {
  $lbwid = $_REQUEST['lbwid'];
  $new_location = "userview.php?user=$lbwid";
  $lbwid = mysql_real_escape_string(trim($lbwid));
  $result = mysql_query("SELECT status, firstname, surname FROM people2 WHERE id='$lbwid'", $db);
  if ($result) {
    $row = mysql_fetch_array($result);

    if ($action == "remove") {
      if (!isset($certain)) {
        HtmlHead("upgrade", "User Removal Verification", $_SESSION["userstatus"], $_SESSION["userid"]);
        echo "Are you certain that you want to irrevocably remove the user " . $row["firstname"] . " " . $row["surname"] . "?";
        ?>
        <br/>
        <form method='post' action='upgrade.php'>
          <input type='hidden' name='lbwid' value='<?php echo $lbwid ?>'/>
          <input type='hidden' name='action' value='remove'/>
          <input type='submit' name='certain' value='no' class='adminbar' style="width: auto"/>
          <input type='submit' name='certain' value='yes' class='adminbar' style="width: auto"/>
        </form>
        <?php
        HtmlTail();
        exit();
      } else if ($certain == "yes") {
        $sql = "DELETE FROM people2 WHERE id='$lbwid'";
        $result = mysql_query($sql, $db);
        $new_location = "users.php";
      } else {
        header("Location: userview.php?user=$lbwid");
        exit();
      }
    } else {
      $field = "status";
      if ($action == "upgrade") {
        $status = $row["status"] << 1;
        if ($status == 0) {
          $status = 1;
        }
      } else if ($action == "downgrade") {
        $status = $row["status"] >> 1;
      } else if ($action == "mark present") {
        $field = "present";
        $status = 1;
      } else {
        echo "<b>Invalid action --$action-- provided<br /></b>";
        HtmlTail();
        exit();
      }

      $sql = "UPDATE people2 SET " . $field . "='$status' WHERE id='$lbwid'";
      $result = mysql_query($sql, $db);
    }

    if (!$result) {
      HtmlHead("upgrade", "Database Error", $_SESSION["userstatus"], $_SESSION["userid"]);
      printf("%s", mysql_error());
      HtmlTail();
    } else {
      header("Location: $new_location");
    }
  }
}
?>
