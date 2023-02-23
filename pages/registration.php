<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();
$url = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = $_POST["icslink"];
    if(str_ends_with($url, ".ics")){
        if(isLoggedIn()) {
            $id = getLoggedInUserId();
            addTimetable($id, $url);
            $events = getTimetable($url);
            foreach ($events as $event) {
                echo($event[0]);
                echo($event[1]);
                echo($event[2]);
            }
            saveTimetable($id, $events);
            echo("  success, added timetable to database:   ");
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
    <div class = "logo">
        <button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
    </div>

</header>

<div class = "modal">
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
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class = "linkcontainer">
                <input type="url" id="icslink" placeholder="Timetable URl" name="icslink" required>
            </div>

            <div class = "buttonbox">
                <button class="buttondesign" id = "post" type = "submit" value = "">Continue</button>
            </div>

        </form>
    </div>
</div>

<footer id ="footer">
    <a href="#privacypolicy">Privacy Policy</a>
    <a href="#t&c">Terms & Conditions</a>
    <a href="#contact">Contact Us</a>
</footer>


</body>
</html>
