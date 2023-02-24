<?php

function redirectIfLoggedIn($path) {
    if (isset($_SESSION['user_id'])) {
        header('Location: '.$path);
        die();
    }
}

function redirectIfNotLoggedIn($path) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: '.$path);
        die();
    }
}

function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    return false;
}

function getLoggedInUserId(): int {
    return ($_SESSION['user_id']);
}