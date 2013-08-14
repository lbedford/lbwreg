<?php
require("basefunc.inc.php");

HtmlHead("resetpassword", "Reset Password", "", "");
?>


<h3>Password reset request form</h3>
<!--
error: <form> isn't allowed in <table> elements errorId: 11, arg1: <form>, arg2: table arg3:
changed by sbolis on Tue May  1 17:48:13 EEST 2007
<table class="reginfo" width="80%">

<form method="POST" action="/remind.php">
    <tr><th width="25%"> First name</th><td width="75%"><input type="text" name="firstname" maxlen="40" size="40"></td></tr>
    <tr><th> Surname   </th><td><input type="text" name="surname" maxlen="40" size="40"></td></tr>
    <tr><td colspan="2" wrap="on" align="center">Your login details will be mailed to the email address that you used when you registered.
     If that has changed, Please enter the address at which you currently receive your lbw list mail.</td></tr>
    <tr><th align="center" wrap="on"> New List address (optional)</th><td><input type="text" name="newmail" size="40" maxlen="60"></td></tr>
    <tr><td colspan="2" align="right"><input type="submit" name="remind" value="submit"></td></tr>
</form>

</table>
-->

<form method="post" action="<?php echo $regpath; ?>/reset.php">
  <label for='firstname'>First name:</label><label>
    <input type="text" name="firstname" size="40"/>
  </label><br/>
  <label for='surname'>Surname:</label><label>
    <input type="text" name="surname" size="40"/>
  </label><br/>
  Your login details will be mailed to the email address that you used when you registered. <br/>
  <input type="submit" name="Reset" value="Submit"/>
</form>



<?php
HtmlTail()
?>
