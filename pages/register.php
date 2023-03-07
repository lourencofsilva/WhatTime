<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();
redirectIfLoggedIn("../index.php");

$name = $username = $email = $password = $confirm = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $realName = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $username = $_POST["username"];
    $password_confirm = $_POST["confirm"];
    $passedValidation = true;

    if (empty($realName)) {
        $error = $error ."Name is required. <br>";
        $passedValidation = false;
    }

    if (empty($username)) {
        $error = $error ."Username is required. <br>";
        $passedValidation = false;
    } else {
        if (checkIfUsernameExists($username)){
            $error = $error ."That username is taken. Please choose another. <br>";
            $passedValidation = false;
        }
    }

    if (empty($email)) {
        $error = $error ."Email Address is required. <br>";
        $passedValidation = false;
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $error .  "Invalid Email Address. <br>";
            $passedValidation = false;
        } else {
            if (checkIfEmailExists($email)){
                $error = $error ."Email Address is already in use. Please log in. <br>";
                $passedValidation = false;
            }
        }
    }

    if (empty($password) or empty($password_confirm)) {
        $error = $error ."Password and Password Confirmation are required. <br>";
        $passedValidation = false;
    } else {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $numbers    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if (!$uppercase || !$lowercase || !$numbers || !$specialChars || strlen($password) < 8) {
            $passedValidation = false;
            $error = $error ."Password must contain at least: 1 Uppercase Character, 1 Lowercase Character, 1 Number, and 1 Special Character<br>";
        } else {
            if ($password_confirm != $password){
                $passedValidation = false;
                $error = $error ."Password and confirmation do not match.";
            }
        }
    }

    if ($passedValidation) {
        createUser($realName,null,$username,$email,$password);
        $user = authenticateUsername($email, $password);
        if ($user != "false") {
            // This function will return the id.
            $_SESSION['user_id'] = $user;
            redirectIfLoggedIn("./registration.php?" .  htmlspecialchars($_SERVER['QUERY_STRING']));
    }
}}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started (Registeration) - Step 1</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/register.css">

    <!--- FAVICONS --->
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">
    <link rel="mask-icon" href="../safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <script type="text/javascript">
        function checkAll() {
            var regexPw = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[~!@#$%^&*()-_+=])[A-Za-z\d~!@#$%^&*()-_+=]{8,128}$/;
            var regexEmail = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
            var em = document.getElementById('email').value;
            var p1 = document.getElementById('password').value;
            var p2 = document.getElementById('confirm').value;
            if (!regexEmail.test(em)) {
                document.getElementById("alert").innerHTML += "This is not a general email format.<br>";
                return false;
            }
            if (!regexPw.test(p1)) {
                document.getElementById("alert").innerHTML += "Password should be of length 8 and contain at least\none uppercase [A-Z]\none lowercase [a-z]\none number [0-9]\none special character [~!@#$%^&*()-_+=]<br>";
                return false;
            }
            if (p1 != p2) {
                document.getElementById("alert").innerHTML += "Passwords do not match.<br>";
                return false;
            }
        }
    </script>
</head>
<body>
<div class="header">
    <button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main_btn" src="../images/logo_white.png"></button>
</div>
<div class="wrapper">
    <div class="info_box">
        <h1 class="info_title">REGISTER</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" onsubmit="return checkAll()">
            <div class="name">
                <input id="name" type="text" name="name" max="30" placeholder="Name" required>
            </div>
            <div class="username">
                <input id="username" type="text" name="username" max="30" placeholder="Username" required>
            </div>
            <div class="email">
                <input id="email" type="email" name="email" max="256" placeholder="Email" required>
            </div>
            <div class="password">
                <input id="password" type="password" name="password" max="128" placeholder="Password" required>
            </div>
            <div class="confirm">
                <input id="confirm" type="password" max="128" name="confirm" placeholder="Confirm Password" required>
            </div>
            <div class="login">
                <p>Already Registered?</p>
                <a href="<?php echo "login.php?" . htmlspecialchars($_SERVER['QUERY_STRING']); ?>">Login Here</a>
            </div>
            <div class="final">
                <input class="continue" id="post" type="submit" value="Continue">
            </div>
            <p id="alert"><?php
                if ($error){
                    echo($error);
                }
                ?></p>
        </form>
    </div>
</div>
<footer>
    <a href="#privacypolicy">Privacy Policy</a>
    <a href="#t&c">Terms & Conditions</a>
    <a href="#contact">Contact Us</a>
</footer>
</body>
</html>