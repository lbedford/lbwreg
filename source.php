<?php
include("basefunc.inc.php");

CheckLoggedInOrRedirect();

if ($_SESSION["userstatus"] < 8) {
  header("Location: " . getenv("HTTP_REFERER"));
}

HtmlHead("source", "sources", $_SESSION["userstatus"], $_SESSION["userid"]);
?>

<div>
  Source code view removed in favour of the <a href="http://github.com/lbedford/lbwreg/">github repository</a>
</div>

<?php

HtmlTail();
?>
