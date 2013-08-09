<script>
  function validatePwd() {
    var pw1 = document.passwdForm.password.value;
    var pw2 = document.passwdForm.verify.value;

    if (pw1 != pw2) {
      alert("Passwords are different");
      return false;
    }
    else {
      return true;
    }
  }
</script>

<?php
require("basefunc.inc.php");

session_start();
HtmlHead("changepassword", "Change Password", $_SESSION["userstatus"], $_SESSION["userid"]);
?>


<h3>Password change request form</h3>

<form name="passwdForm" method="post" onSubmit="return validatePwd()" action="<?php echo $regpath; ?>/change.php">
  <label>
    Old Password:
    <input type="password" name="old_password" size="40"/>
  </label><br/>
  <label>
    New Password:
    <input type="password" name="password" size="40"/>
  </label><br/>
  <label>
    Verify Password:
    <input type="password" name="verify" size="40"/>
  </label><br/>
  <input type="submit" name="Change" value="Submit"/>
</form>



<?php
HtmlTail()
?>
