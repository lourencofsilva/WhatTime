<?php

function openConn(): PDO {
    $user = "h14965lf";
    $host = "dbhost.cs.man.ac.uk";
    $pass = "_UL37f734XT6NKJs8Cc9MMyBey7+wz";
    $name = "2022_comp10120_x3";
    try
    {
        $pdo = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_WARNING);
    }
    catch (PDOException $pe)
    {
        die("Could not connect to $host :" . $pe->getMessage());
    }
    echo ("Connected");
    return $pdo;
}

function closeConn($obj): void
{
    $obj = null;
    echo "Closed";
}

function showDB(): void
{
    $pdo = openConn();
    $sql = "SELECT * FROM users";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    while ($row = $stmt->fetch())
    {
        print("<h3>" . $row['name'] . "</h3>");
        print("<h3>" . $row['username'] . "</h3>");
        print("<h3>" . $row['password_hash'] . "</h3>");
        print("<h3>" . $row['email'] . "</h3>");
        print("<h3>" . '<img alt="Profile Picture" src="data:image/png;base64,'.base64_encode($row['profile_picture']).'"/>' . "</h3>");
    }
    closeConn($pdo);
}

function createUser($name, $profile_picture, $username, $email, $password) {
    $pdo = openConn();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, profile_picture, username, email, password_hash)
 VALUES (:name, :profile_picture, :username, :email, :password_hash)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'profile_picture' => $profile_picture,
        'username' => $username,
        'email' => $email,
        'password_hash' => $password_hash
    ]);
    closeConn($pdo);
}

function authenticateUser($email, $password): bool
{
    $pdo = openConn();

    $sql = "SELECT password_hash
            FROM users
            WHERE email= :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (password_verify($password, $row['password_hash'])) {
        echo("authentication successful");
        return true;
    }
    echo("incorrect email or password");
    return false;
}

showDB();
//createUser("Lourenco1", file_get_contents("https://i.imgur.com/UYcHkKD.png" ), "lourencofsilva1", "fbhifhef@fhjjff1.com", "12345678");
//showDB();
authenticateUser("fbhifhef@fhjjff.com", "12345678");
?>