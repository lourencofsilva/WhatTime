<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();

if (isLoggedIn()) {
    if ($_GET["endpoint"] == "profile-events" and isset($_GET["event"])) {
        makeEventInactiveAPI(htmlspecialchars($_GET["event"]), getLoggedInUserId());
    }

    if ($_GET["endpoint"] == "dashboard-create-group" and isset($_GET["name"])) {
        if (!($response = createGroup(htmlspecialchars($_GET["name"]), getLoggedInUserId()))) {
            echo "An error ocurred in creating the group. Make sure that you aren't a member of another group with the same name, or try again later.";
        } else {
            echo "Group Created Successfully!<br><br> Share the link below with anyone you want to join your group!<br>Invite Link: " . $response;
        }
    }
}