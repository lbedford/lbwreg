<?php
    include("basefunc.inc.php");
     
    session_start();
    if (!array_key_exists("userid", $_SESSION)) {
        header("Location: login.php"); exit();
    }
     
    $db = connectmysql();
     
    $sql = "SELECT lbwid,galpix,firstname,surname FROM whois,people2 where (id=lbwid) AND (people2.status>1) AND (people2.attending > 0)  ORDER by firstname,surname";
     
    $result = mysql_query($sql, $db);
    //if (!$result){ echo mysql_error($db); exit();}
     
    HtmlHead("gallery", "Picture Gallery", $_SESSION["userstatus"], $_SESSION["userid"]);
     
    echo "<table class='reginfo'><tr>\n";
     
    $cc = 0;
    $columns = 3;
     
    while ($row = mysql_fetch_array($result)) {
        if ($cc == $columns) {
            printf("<tr>\n");
            $cc = 0;
        }
        printf("<TD align=\"center\"><A HREF=\"userview.php?user=%d\"><img src=\"pictures/%s\" height=200 width=150></a><br>%s %s</td>\n", $row["lbwid"], $row["galpix"], $row["firstname"], $row["surname"]);
        $cc++;
        if ($cc == $columns)
            printf("</tr>\n");
    }
    if ($cc < $columns) {
        while ($cc++ < $columns)
        printf("<td>&nbsp;</td>");
        printf("</tr>\n");
    }
    echo "</table><br>\n<hr />";
    echo "To be included email us a portrait format photo width=150,height=200, saying who you are.<br>\n";
    HtmlTail();
?>

