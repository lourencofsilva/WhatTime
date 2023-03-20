<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["invite_id"])) {
        $invite_id = htmlspecialchars($_POST["invite_id"]);
        redirectIfNotLoggedIn("./login.php?redirect=invite.php?id=" . $invite_id);
    } else {
        echo "This invite link does not exist or is no longer valid.";
        die();
    }

} else {
    if (isset($_GET["id"])) {
        $invite_id = htmlspecialchars($_GET["id"]);
        redirectIfNotLoggedIn("./login.php?redirect=pages/invite.php?id=" . $invite_id);
    } else {
        echo "This invite link does not exist or is no longer valid.";
        die();
    }
}
$info = getGroupInfoFromInviteLink($invite_id);

if (!$info) {
    echo "This invite link does not exist or is no longer valid.";
    die();
}

$group_id = $info["id"];
$group_name = $info["name"];


foreach (getUserGroupInfo(getLoggedInUserId()) as $group) {
    if ($group["id"] == $group_id) {
        echo "You are already a part of this group.";
        die();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    addUserToGroup($user_id, $group_id);
    redirectIfLoggedIn("./dashboard.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Group Invite · WhatTime?</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/invite.css">

    <!--- FAVICONS --->
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">
    <link rel="mask-icon" href="../safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
</head>
<body>

<header>
    <div class = "logo">
        <button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
    </div>

</header>

<div class = "wrapper">
    <div class = "modal">
        <div class = "firstline">
            <p>JOIN GROUP</p>
        </div>
        <div class = "inviteinfo">
            <p>You were invited to join the group <?php echo htmlspecialchars($group_name)?><br><br></p>
            <p>Would you like to join this group?<br></p>
        </div>

        <div class = "inputbox">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

                <input type="hidden" name="invite_id" value="<?php echo $invite_id;?>">

                <div class = "buttonbox">
                    <button class="buttondesign" id = "post" type = "submit" value = "">Join Group</button>
                </div>

            </form>
        </div>
    </div>
</div>

<footer id ="footer">
    <a href="#privacypolicy">Privacy Policy</a>
    <a href="#t&c">Terms & Conditions</a>
    <a href="#contact">Contact Us</a>
</footer>


</body>
</html>
