<?php

include "database.php";
include "user-session.php";

session_start();
redirectIfLoggedIn("./index.php");

$email = $password = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passedValidation = true;

    if (empty($email)) {
        $error = $error ."Email Address is required. <br>";
        $passedValidation = false;
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $error .  "Invalid Email Address. <br>";
            $passedValidation = false;
        }
    }

    if (empty($password)) {
        $error = $error ."Password is required. ";
        $passedValidation = false;
    }

    if ($passedValidation) {
        $user = authenticateUsername($email, $password);
        if ($user != "false") {
            // This function will return the username, even if email was used for login.
            echo ("successful. now set cookie time, congrats :)");
            $_SESSION['user'] = $user;
            redirectIfLoggedIn("./index.php");
        }
        else {
            $error = $error . ("incorrect email/password.");
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Log In</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="../css/login.css">
</head>
<body>
	<div class="header">
		<button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main_btn" src="../images/logo_white.png"></button>
	</div>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
	<div class="login_box">
		<h1 class="info_title">LOG IN</h1>
		<div class="email">
			<input name="email" id="email" type="email" max="256" placeholder="Email" value="<?php echo $email;?>">
		</div>
		<div class="password">
			<input name="password" id="password" type="password" max="128" placeholder="Password">
		</div>
		<div class="register">
			<p>First Time Here?</p>
			<a href="register.html">Register Now</a>
		</div>
		<div class="final">
			<input class="continue" id="post" type="submit" value="Login">
		</div>

	</div>
    </form>
    <?php
    if ($error){
        echo("<p>". $error ."</p>");
    }
    ?>
	<footer>
	    <a href="#privacypolicy">Privacy Policy</a>
	    <a href="#t&c">Terms & Conditions</a>
	    <a href="#contact">Contact Us</a>
	</footer>
</body>
</html>