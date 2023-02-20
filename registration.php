<?php

include "database.php";
include "user-session.php";

session_start();
$url = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = $_POST["icslink"];
    if(str_ends_with($url, ".ics")){
        if(isLoggedIn()) {
            $id = getLoggedInUserId();
            addTimetable($id, $url);
            $events = getTimetable($url);
            saveTimetable($id, $events);
            echo("  success, added timetable to database:   ");
            //redirectIfLoggedIn("./index.php");
        }
        else{
            echo("please log in first, then try again");

        }
    }
    else{
        echo("that isnt an .ics file, try again g");
    }
}
echo($url);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/registration.css">
</head>
<body>

  <header>
    <button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
  </header>

  <div class = "modal">
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class = "firstline">
          <p>SYNC YOUR TIMETABLE</p>
      </div>
      <div class = uploadcalendar>
        <p>To upload your calendar locate the link to the ics file of your calendar system and paste it below.</p>
      </div>
      <div class = moreinfo>
        <p>More details about how to do this can be found <a href="#moreinfo"><b>here.</b></a></p>
      </div>

      <div class = "inputbox">
        <input type="url" id="icslink" placeholder="Timetable URl" name="icslink">
      </div>
      <div class = "buttonbox">
          <input class="continue" id="post" type="submit" value="Continue">
      </div>
      </form>
  </div>

  <footer>
    <a href="#privacypolicy">Privacy Policy</a>
    <a href="#t&c">Terms & Conditions</a>
    <a href="#contact">Contact Us</a>
  </footer>

</body>
</html>
