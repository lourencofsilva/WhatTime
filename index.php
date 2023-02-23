<?php

include "php_funcs/user-session.php";

session_start();
redirectIfLoggedIn("./pages/profile.html");
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
			<div class="hero">
				<h2><a href="#">TIME THAT WORKS FOR EVERYONE</a></h2>
			</div>
		</div>
		<div class="main">
			<div class="description">
				<p><a href="#">A tool to simplify your group meetings. Sync your timetables and find time that works for everyone.</a></p>
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
			<div class="footer">
				<a>©</a>
				<ul>
					<li><a href="#">Contact US</a></li>
					<li><a href="#">Terms & Conditions</a></li>
					<li><a href="#">Privacy Policy</a></li>
				</ul>
			</div>
		</div>
	</div>
</body>
</html>