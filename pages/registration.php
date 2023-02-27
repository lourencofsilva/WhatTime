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
            saveTimetable($id, $events);
            if (isset($_GET["redirect"])) {
                redirectIfLoggedIn("../" . htmlspecialchars($_GET["redirect"]));
            } else {
                    redirectIfLoggedIn("../index.php");
                }
            }
        else{
            echo("please log in first, then try again");
        }
    }
    else{
        echo("that isnt an .ics file, try again g");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/registration.css">
    <script type="text/javascript">
        function checkURL() {
            var regex =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
            var url = document.getElementById('icslink').value;
            if (regex.test(url)){
            return true;
            }
            else{
                alert("Invalid URL, Please try again.");
            return false;
            }
        }
    </script>
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
        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" onsubmit="return checkURL()">
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
