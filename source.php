<?php
include("basefunc.inc.php");

/* variables from environment (GET/POST) */
$files = $_REQUEST["files"];

$_SESSION["userid"] = 0;
session_start();
if (!$_SESSION["userid"]) {
  header("Location: login.php");
  exit();
}
if ($_SESSION["userstatus"] < 8) {
  header("Location: " . getenv("HTTP_REFERER"));
}

HtmlHead("source", "PHP Source <small>" . $filename . "</small><br /><br />" . $files, $_SESSION["userstatus"], $_SESSION["userid"]);
?>

<div>
  Source code view removed in favour of the <a href="http://root.killefiz.de/svn/lbw/">svn repository</a>
</div>

<?php

HtmlTail();
?>
