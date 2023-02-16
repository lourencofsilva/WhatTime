<?php

include "database.php";
include "user-session.php";

session_start();

if (isLoggedIn()) {
    echo getLoggedInUser();
    echo "<a href='logout.php'>LOGOUT</a>";
    echo "<a href='registration.php'>Add timetable</a>";
} else {
    echo "<a href='login.php'>LOGIN</a>";
}
