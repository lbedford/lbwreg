<?php
require("basefunc.inc.php");

$_SESSION["userid"] = 0;
session_start();
if (array_key_exists("userid", $_SESSION)) {
  header("Location: welcome.php");
  exit();
}

HtmlHead("login", "Login", "", "");
include("html/loginform.html");
?>
<iframe width="600px" height="200px"
        src="analytics/index.php?module=CoreAdminHome&action=optOut&language=en"></iframe>
<?php
HtmlTail();
?>

