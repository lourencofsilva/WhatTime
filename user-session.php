<?php

function redirectIfLoggedIn($path) {
    if (isset($_SESSION['user'])) {
        header('Location: '.$path);
        die();
    }
}

function isLoggedIn() {
    if (isset($_SESSION['user'])) {
        return true;
    }
    return false;
}

function getLoggedInUser(): string {
    return ($_SESSION['user']);
}