<?php
    include("basefunc.inc.php");

    $db = ConnectMysql();

    /* variables from the environment (GET/POST) */
    $logon = mysql_real_escape_string($_REQUEST["logon"]);
    // No need to escape the password, because we never pass it to mysql.
    $password = $_REQUEST["password"];

    if (!$logon || !$password) {
        header("Location: login.php");
	exit();
    }

    // get the password from the database
    $query = "SELECT password,id FROM people2 WHERE logon = '$logon'";

    $result = mysql_query($query, $db);

    if (!$result)
        printf("%s<br>\n", mysql_error());

    $row = mysql_fetch_array($result);

    if ($row["password"] == $password) 
    {
      // password is correct, but not crypted
      $crypted_password = crypt($password);
      $password_update_sql = "UPDATE people2 SET password=\"".$crypted_password.
          "\" where id=".$row["id"];

      $result = mysql_query($password_update_sql, $db);

      if (!$result)
        printf("%s<br>\n", mysql_error());
    }

    // get the details from the database
    $query = "SELECT id,firstname,surname,password,status,logons FROM people2 WHERE logon = '$logon'";

    $result = mysql_query($query, $db);

    if (!$result)
        printf("%s<br>\n", mysql_error());

    $row = mysql_fetch_array($result);

    $stored_password = $row["password"];
    $crypted_password = crypt($password, $stored_password);

    if (crypt($password,$stored_password) == $stored_password)
    {
        if ($row["status"] < 2) {
	    HtmlHead("verify", "No access", $_SESSION["userstatus"], $_SESSION["userid"]);
	    
            echo "<h3>Sorry.  You don't seem to have access rights to this site yet.<br>\n";
            echo "Please contact an administrator to have your access approved.</h3><br><hr>";
            HtmlTail();
            exit();
        }

        session_start();
        $session = session_id();
         
        $_SESSION["userid"] = $row["id"];
        $_SESSION["userstatus"] = $row["status"];
        $_SESSION["username"] = $row["firstname"]." ".$row["surname"];
        $_SESSION["userforum"] = 1;
         
        $logons = 1;
        if (array_key_exists("logons", $row)) {
          $logons = intval($row["logons"])+1;
        }
          
        $now = time();
         
        mysql_query("UPDATE people2 set logons='$logons', laston='$now' WHERE id='".$_SESSION["userid"]."'", $db);
         
        header("Location: /welcome.php");
        ?>
        <a href="/welcome.php">continue</a>
        <?php
    } else {
	HtmlHead("Verify", "Login Failed", "", "");
        echo "<h2>Login rejected<p>";
        echo "Either your login  or password were incorrect<br>";
        echo "</p></h2>";
        echo "<form method=get action=\"login.php\"><input type=submit value=\"Try Again\"></form><br>";
        
        HtmlTail();
    }
?>
