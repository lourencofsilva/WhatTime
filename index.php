<?php

include "database.php";
include "user-session.php";

session_start();

if (isLoggedIn()) {
    echo getLoggedInUserId();
    echo "<a href='pages/logout.php'>LOGOUT</a>";
    echo "<a href='pages/registration.php'>Add timetable</a>";
} else {
    echo "<a href='pages/login.php'>LOGIN</a>";
}
