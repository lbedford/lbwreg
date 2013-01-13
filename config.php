<?php # -*- php -*-

# When the event starts
$year = 2013;
$month = 8;
$day = 17;
$duration = 9;

$timeZone = 'Europe/Vienna';

# Where the event is
$location = "Castleton (Honeypot Village), Derbyshire";

# Website deployment information
$regpath = "http://lbwreg.draiocht.net";
$eventhost = "http://lbw2013.norgie.net";
$teammail = "lbw2013@draiocht.net";
$listmail = "lbw@x31.com";
$frommail = "lbwreg@draiocht.net";

# T-shirt information
$Products = array();

// level => price
$Prices = array(0 => 20);

$Sizes = array("M - Small",
               "M - Medium",
               "M - Large",
               "M - XL",
               "M - 2XL",
               "M - 3XL",
               "M - 4XL",
               "M - 5XL",
               "W - Small",
               "W - Medium",
               "W - Large",
               "W - XL");

$menu = array(
//      pagename              caption            passlevel
  array("welcome.php",        "Welcome",         2),
  array("activities.php",     "Activities",      2),
  array("schedule.php",       "Schedule",        2),
  array("t-shirts.php",       "T-shirts",        2),
  array("participants.php",   "Participants",    2),
  array("gallery.php",        "Gallery",         2),
  array("rides.php",          "Rides",           4),
  array("changepassword.php", "Change Password", 2),
  array("logout.php",         "Logout",          2),
);

$pixupdate_string = "<span title='Place a picture file named mid_&lt;year&gt;_&lt;userid&gt;.jpg in reg/gallery/, then do update...'>Pix-update</span>";

$admin_menu = array(
//      pagename              caption            passlevel
  array("updategallery.php",  $pixupdate_string, 8),
  array("users.php",          "Users" ,          8),
  array("source.php",         "Source",          8),
);

# Event type max length
$eventmaxhours = array(1 => 24, 2 => 4, 3 => 8, 4 => 8);

# The types of accomodation
$acctype[0] = "unknown";
$acctype[1] = "Hotel";
$acctype[2] = "FerienWohnung";
$acctype[3] = "Hotel Garni";
$acctype[4] = "Pension";
$acctype[5] = "Campsite";
$acctype[6] = "Caravan";
$acctype[7] = "Boat";
$acctype[8] = "Other";

# An added lookup table to give the order in which we should show the
# types above
$accorder[0] = 0;
$accorder[1] = 1;
$accorder[2] = 2;
$accorder[3] = 7;
$accorder[4] = 3;
$accorder[5] = 4;
$accorder[6] = 5;
$accorder[7] = 6;

# The types of transport
$xport[0] = "unknown";
$xport[1] = "Car";
$xport[2] = "Caravan";
$xport[3] = "Plane";
$xport[4] = "Train";
$xport[5] = "Bike";
$xport[6] = "Other";
$xport[7] = "Ship";
$xport[8] = "Rocket";
