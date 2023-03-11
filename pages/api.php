<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();

if (isset($_GET["event"]) and isLoggedIn()) {
    makeEventInactiveAPI($_GET["event"], getLoggedInUserId());
}