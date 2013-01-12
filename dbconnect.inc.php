<?php # -*- php -*-

    # We only check a .sample of this file into the svn repostitory
    # to make sure that one years database-connection settings don't
    # end up on a public svn repository

    function ConnectMysql() {
        $db = mysql_connect("localhost", "lbw", "lbw2008");
        if (!$db) {
            printf("Connection refused<BR>Please try later<BR><HR><BR>");
            exit();
        }
        if (!mysql_select_db("lbw2008", $db)) {
            printf("No such database");
            exit();
        }
        return $db;
    }
?>
