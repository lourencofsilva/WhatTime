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
            http_response_code(201);
        }
    }

    if ($_GET["endpoint"] == "dashboard-group-delete" and isset($_GET["group-id"])) {
        deleteGroupAPI(htmlspecialchars($_GET["group-id"]), getLoggedInUserId());
    }

    if ($_GET["endpoint"] == "dashboard-change-name" and isset($_GET["group-id"]) and isset($_GET["new-name"])) {
        changeNameAPI(htmlspecialchars($_GET["group-id"]), htmlspecialchars($_GET["new-name"]), getLoggedInUserId());
    }

    if ($_GET["endpoint"] == "dashboard-member-delete" and isset($_GET["group-id"]) and isset($_GET["member-id"])) {
        deleteMemberAPI(htmlspecialchars($_GET["group-id"]), htmlspecialchars($_GET["member-id"]), getLoggedInUserId());
    }

    if ($_GET["endpoint"] == "dashboard-create-event" and isset($_GET["title"]) and isset($_GET["start"]) and isset($_GET["end"]) and isset($_GET["group-id"])) {
        createEventAPI(htmlspecialchars($_GET["title"]), htmlspecialchars($_GET["start"]), htmlspecialchars($_GET["end"]), htmlspecialchars($_GET["group-id"]), getLoggedInUserId());
    }

    if ($_GET["endpoint"] == "onboarding-disable" and isset($_GET["type"])) {
        onboardingDisableAPI(htmlspecialchars($_GET["type"]), getLoggedInUserId());
    }
}