<?php
    require("basefunc.inc.php");

    function sendEmailToList($event) {
        global $listmail, $frommail, $year;

        $ownermail = getForumOwnerEmail($event);
        $event_name = getNameOfEvent($event);
        $event_description = getDescriptionOfEvent($event);
        mail($listmail, "New event proposed for LBW $year",
            "New event:\n\n".
            "  Name: $event_name\n".
            "\n".
            "  Description: $event_description".
            "\n".
            "Regards,\n\n".
            "  The LBW $year registration site", "From: $frommail");
    }

    session_start();
    if (!array_key_exists("userid", $_SESSION)) {
        header("Location: login.php"); exit();
    }

    extract($_REQUEST, EXTR_SKIP);

    if (!isset($option)) {
      if ($type > 0) {
        $option = "propose";
      }
    }

    switch($option) {
        case "ABORT":
	        header("Location: activities.php");
        	exit();
        case "Save":
	        $db = ConnectMysql();
       		$now = time();
        	$sql = sprintf("INSERT INTO Events (type, owner, name, schedtxt, description, number, forum_duration, created, status)".
                               " VALUES (%d,%d,'%s','%s','%s','%s',%d,%d,%d)",
                               $type, $_SESSION["userid"],
                               mysql_real_escape_string(trim($heading)),
                               mysql_real_escape_string(trim($schedtxt)),
                               mysql_real_escape_string(trim($description)),
                               mysql_real_escape_string(trim($number)),
                               $forum_duration, $now, 256);
        	if ($result = mysql_query($sql, $db)) {
            	  $event = mysql_insert_id($db);
            	  $result = mysql_query("INSERT INTO eventreg (geek,event) VALUES ('".$_SESSION["userid"]."','$event')", $db);
                  if (!$result) {
                    echo "Insert into eventreg failed: " . mysql_error($db). "<br>\n";
                  }
        	} else {
                  echo "Insert into events failed: " .mysql_error($db) . "<br>\n";
                }
        	if (!$event) {
            	  header("Location: activities.php");
                }
        	header("Location: forum.php?forum=$event");
                sendEmailToList($event);
         	exit();
        case "propose":
        	HtmlHead("propose", "Project Proposal", $_SESSION["userstatus"], $_SESSION["userid"]);
         	echo "";
        	switch ($type) {
            		case 1: //     workshop
            			$contrib = "organise a workshop";
            			$shortname = "Workshop Name";
            			$longtext = "Here you should enter a more detailed description of what you intend to achieve. You should mention any reading material that users might wish to study before coming.";
            			$shorttext = "A name for the project";
            			break;
            		case 2: //     lecture
            			$contrib = "give a lecture";
            			$shortname = "Lecture Title";
            			$longtext = "Here you should enter a more detailed description of what you intend to cover.  You should state whether any previous knowledge of the subject is required.  You should mention any reading material that users might wish to study before coming.";
            			$shorttext = "A title for the lecture";
            			break;
            		case 3: //     excursion
            			$contrib = "organise an excursion or hike";
            			$shortname = "Excursion Title";
            			$longtext = "Here you should enter a more detailed description of the type of excursion proposed.  You should state any requirements (experienced climbers only etc.).";
            			$shorttext = "A name for the excursion";
            			break;
            		case 4: //     Community event
            			$contrib = "organise a community event";
            			$shortname = "Event Title";
            			$longtext = "Here you should enter a more detailed description of the type of event proposed.  You should state any special requirements.";
            			$shorttext = "A title for the event";
            			break;
        	}
		echo "<h2>The organisers thank ".$_SESSION["username"]." for offering to $contrib</h2><br>\n";
        	echo "<FORM METHOD=POST>\n";
        	echo "<INPUT TYPE=HIDDEN NAME=type VALUE=$type>\n";
        	echo "$shortname<br>";
        	echo "<INPUT TYPE=TEXT NAME=\"heading\" VALUE=\"$shorttext\"  SIZE=50 MAXLEN=50><br>";
        	if ($type>1) {
            		echo "Short name (for schedule diary entry)<br>";
            		echo "<INPUT TYPE=TEXT NAME=\"schedtxt\" VALUE=\"\"  SIZE=12 MAXLEN=12><br>";
        	}
        	echo "Details (Use HTML in this field) <br>\n";
	        echo "<TEXTAREA NAME=description COLS=60 ROWS=10>\n";
       		echo "$longtext";
        	echo "</textarea><br>\n";
        	switch ($type) {
    		        case 1:
				echo "<INPUT TYPE=HIDDEN NAME=number VALUE=7>\n";
				echo "<INPUT TYPE=HIDDEN NAME=forum_duration VALUE=24>\n";
				break;
            		case 2:
				echo "I would like to have <SELECT name=\"sessions\"><OPTION VALUE=1>1\n<OPTION VALUE=2>2\n<OPTION VALUE=3>3\n<OPTION VALUE=4>4\n</select> Sessions of ";
				echo "<select name=\"forum_duration\">";
				for($i = 1; $i <= $eventmaxhours[$type] ; $i++) {
		    			$s = ($i == 3) ? "selected" : "";
		    			echo "<option value='$i' $s> $i" ;
				}
				printf("</select> hours duration<br>\n");
				break;
	    		case 3:
				echo "<INPUT TYPE=HIDDEN NAME=number VALUE=1>\n";
				printf("The Event will be about <select name='forum_duration' >");
				for($i = 1; $i <= $eventmaxhours[$type] ; $i++) {
		    			$s = ($i == 3) ? "selected" : "";
		    			echo "<option value='$i' $s> $i" ;
				}
				printf("</select> hours duration<br>\n");
				break;
	    		case 4:
				echo "<INPUT TYPE=HIDDEN NAME=number VALUE=1>\n";
				printf("The Event will be about <SELECT NAME=forum_duration>");
				for($i = 1; $i <= $eventmaxhours[$type] ; $i++) {
		    			$s = ($i == 3) ? "selected" : "";
		    			echo "<option value='$i' $s> $i" ;
				}
				printf("</select> hours duration<br>\n");
				break;
		}
        	echo "<INPUT TYPE=SUBMIT NAME=option VALUE=Save>&nbsp;<INPUT TYPE=SUBMIT NAME=option VALUE=ABORT>\n";
        	echo "</form><br><hr>";
    }
    HtmlTail();
?>
