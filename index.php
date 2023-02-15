<?php

include "database.php";
include "user-session.php";

session_start();

if (isLoggedIn()) {
    echo getLoggedInUser();
    echo "<a href='logout.php'>LOGOUT</a>";
} else {
    echo "<a href='login.php'>LOGIN</a>";
}
