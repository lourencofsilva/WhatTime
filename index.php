<?php

include "php_funcs/database.php";
include "php_funcs/user-session.php";

session_start();

if (isLoggedIn() && !checkTimetableExists(getLoggedInUserId())) {
    redirectIfLoggedIn("./pages/registration.php");
}
redirectIfLoggedIn("./pages/dashboard.php");
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Home(Logged Out)</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="./css/index.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!--- FAVICONS --->
    <link rel="apple-touch-icon" sizes="180x180" href="./apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicon-16x16.png">
    <link rel="manifest" href="./site.webmanifest">
    <link rel="mask-icon" href="./safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
	<div class="wrap">
		<div class="intro">
			<div class="header">
				<button class="mainlogo" onClick="window.location.reload()" id="btn" type="button"><img class="main-img" src="./images/logo_white.png"></button>
				<div class="nav">
					<ul>
						<li><a href="./pages/register.php">Get Started</a></li>
					</ul>
					<button class="profile" onclick="window.location.href='./pages/login.php'"><i class="fa-regular fa-user"></i></button>
				</div>
			</div>
		</div>
		<div class="main">
			<div class="hero">
				<h2>TIME THAT WORKS FOR EVERYONE</h2>
			</div>
			<!-- <div class="main"> -->
			<div class="description">
				<p>A tool to simplify your group meetings. Sync your timetables and find time that works for everyone.</p>
			</div>
			<div class="bullets">
				<ul class="one">
					<li><a href="#">Automatically updated timetable</a></li>
					<li><a href="#">Create groups</a></li>
				</ul>
				<ul class="two">
					<li><a href="#">Invite your peers</a></li>
					<li><a href="#">Include working hours</a></li>
				</ul>
			</div>
		</div>
		<div class="footer">
			<a>©</a>
			<ul>
				<li><a href="#">Contact US</a></li>
				<li><a href="#">Terms & Conditions</a></li>
				<li><a href="#">Privacy Policy</a></li>
			</ul>
		</div>
	</div>
</body>

</html>