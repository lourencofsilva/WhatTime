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

function getLoggedInUsersID(): int {
    $pdo = openConn();
    $email = ($_SESSION['email']);
    $sql = "SELECT id
            FROM users
            WHERE email= :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();
    return($row['id']);
}