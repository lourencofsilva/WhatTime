<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function openConn(): PDO {
    require_once('../config.inc.php');


    $name = "2022_comp10120_x3";
    try
    {
        include '../config.inc.php';
        $pdo = new PDO("mysql:host=$database_host;dbname=$name", $database_user, $database_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_WARNING);
    }
    catch (PDOException $pe)
    {
        die("Could not connect to $database_host :" . $pe->getMessage());
    }
    return $pdo;
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
        print("<h3>" . "id: " . $row['id'] . "</h3>");
        print("<h3>" . "Name: " . $row['name'] . "</h3>");
        print("<h3>" . "Username: " . $row['username'] . "</h3>");
        print("<h3>" . "passHash: " . $row['password_hash'] . "</h3>");
        print("<h3>" . "email: " . $row['email'] . "</h3>");
        print("<h3>" . '<img alt="Profile Picture" src="data:image/png;base64,'.base64_encode($row['profile_picture']).'"/>' . "</h3>");
    }
    $pdo = null;
}

function addTimetable($id, $timetable_url){
    $pdo = openConn();

    $sql = "UPDATE users 
    SET timetable_url = :timetable_url
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
            'timetable_url' => $timetable_url,
            'id' => $id
        ]);



    $pdo = null;
}
function createUser($name, $profile_picture, $username, $email, $password) {
    if(checkIfUsernameExists($username)){
        return;
    }
    if(checkIfEmailExists($email)){
        return;
    }
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
    $pdo = null;
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
        $pdo = null;
        return true;
    }
    return false;


}

function checkIfEmailExists($email): bool
{
    $pdo = openConn();
    $sql = "SELECT email
            FROM users
            WHERE email = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (isset($row['email'])) {
        $pdo = null;
        return(true);
    }
    else {
        $pdo = null;
        return(false);
    }
}

function checkIfUsernameExists($username): bool
{
    $pdo = openConn();
    $sql = "SELECT username
            FROM users
            WHERE username = :username";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (isset($row['username'])) {
        $pdo = null;
        return(true);
    }
    else {
        $pdo = null;
        return(false);
    }
}
function authenticateUsername($username, $password): int
// This function will return the id, can accept email or username.
// Returns either the user id, or -1 if errored.
{
    $pdo = openConn();

    $sql = "SELECT password_hash, id
            FROM users
            WHERE username= :username OR email= :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'email' => $username
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();
    if (isset($row['password_hash'])) {
        if (password_verify($password, $row['password_hash'])) {
            $pdo = null;
            return ($row['id']);
        }
        else {
            $pdo = null;
            return (-1);
        }
    }
    else{
        $pdo = null;
        return(-1);

    }


}


function getTimetable($url) {
    $fileContent = file_get_contents($url);
    if ($fileContent == false) {
        return(false);
    }
    if(str_starts_with($fileContent, "BEGIN:VCALENDAR")) {
        $events = parseTimetable($fileContent);
        return($events);
    }
    else {
        return(false);
    }
}

function parseTimetable($fileContent) :array {
    /**
     * returns all events found in $fileContent, in a 2d array.
     * output: 2d array, where array[n] = childArray[summary of event n, start date+time of event n, end date+time of event n]
     * its messy i know
     */
    $begins = [];
    $ends = [];
    $dt_starts = [];
    $dt_ends = [];
    $summary = [];
    $AllEvents = [];


    $offset=0;
    $finish = '';
    $find="BEGIN:VEVENT"; //finds index of all event starts in string
    $find_length=  strlen($find);
    while ($string_position = strpos($fileContent, $find, $offset)) {
        $begins[] = $string_position;
        $offset=$string_position+$find_length;
    }

    $find="END:VEVENT"; //finds index of all event ends in string
    $find_length=  strlen($find);
    $offset=0;
    while ($string_position = strpos($fileContent, $find, $offset)) {
        $ends[] = $string_position;
        $offset=$string_position+$find_length;
    }

    for($i=0;$i<count($begins);$i++){
        $current = substr($fileContent, $begins[$i], $ends[$i]);

        $pos = strpos($current, "SUMMARY:") + 8;
        $line = substr($current, $pos);
        $summary[] = substr($current, $pos, $pos + strpos($line, PHP_EOL) - (strpos($current, "SUMMARY:") + 8));

        $pos = strpos(substr($current, strpos($current, "DTSTART") + 7), ":") + 1 + strpos($current, "DTSTART") + 7;
        $line = substr($current, $pos);
        $eventStartTime = substr($current, $pos, $pos + strpos($line, PHP_EOL) - $pos);
        $dt_starts[] = substr($eventStartTime, 0, 4) . '-' . substr($eventStartTime, 4, 2) . '-' . substr($eventStartTime, 6, 2) . ' ' . substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";

        $pos = strpos(substr($current, strpos($current, "DTEND") + 5), ":") + 1 + strpos($current, "DTEND") + 5;
        $line = substr($current, $pos);
        $eventStartTime = substr($current, $pos, $pos + strpos($line, PHP_EOL) - $pos);
        $dt_ends[] = substr($eventStartTime, 0, 4) . '-' . substr($eventStartTime, 4, 2) . '-' . substr($eventStartTime, 6, 2) . ' ' . substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";

        $AllEvents[] = [$summary[$i],$dt_starts[$i], $dt_ends[$i]]; //adds all current event information (summary,start,end) to AllEvents array.
    }
    //echo(var_dump($AllEvents));
    return($AllEvents);
}

function saveTimetable($user_id, $events) {
    $pdo = openConn();

    $sql = "DELETE FROM events WHERE user_id=:user_id"; // Delete current events first
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id,
    ]);

    $sql = "INSERT INTO events (user_id, active, summary, dt_start, dt_end)
VALUES (:user_id, :active, :summary, :dt_start, :dt_end)";

    $stmt = $pdo->prepare($sql);

    $fails = 0; //Check if the inserts were successful
    $errors = array();

    foreach ($events as $event) {
        $result = $stmt->execute([
            'user_id' => $user_id,
            'active' => true,
            'summary' => $event[0],
            'dt_start' => $event[1],
            'dt_end' => $event[2]
        ]);
        if (!$result) {
            $fails++;
            $errors[] = implode(error_get_last());
        }
    }
    echo($fails);

    if ($fails == 0) { // Set last_updated_date to the current time, or log if failed adding events.
        $sql = "UPDATE users SET timetable_last_updated='" . date('Y-m-d H:i:s') . "' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'id' => $user_id,
        ]);
    } else {
        doLog("ERROR", "Adding events to db failed.", implode($errors), "background.php", $user_id);
    }

    $pdo = null;
}


function createGroup($name, $group_picture) {
    $groupUID = generateUID();
    $pdo = openConn();


    $sql = "INSERT INTO `groups` (name, groupUID)
 VALUES (:name, :groupUID)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        //'group_picture' => $group_picture,
        'groupUID' => $groupUID
    ]);



    $pdo = null;
    echo("group created with UID: " . $groupUID);
    return(true);
}
function getGroupInfoFromInviteLink($groupUID) {//I THINK THE PROBLEM IS IN HERE (TRY TO LOAD THIS LINK):
    //https://web.cs.manchester.ac.uk/q98040ac/X3GroupProject/pages/invite.php?id=a1465b7a81ac4f09b5a2ad2f0a094026
    //GOOD LUCK
    $pdo = openConn();

    $sql = "SELECT id, name
            FROM `groups`
            WHERE groupUID = :groupUID";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'groupUID' => $groupUID
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    $pdo = null;

    if (isset($row['id'])) {
        return(array('id' => $row['id'], 'name' => $row['name']));
    }
    else {
        return(false);
    }
}

function addUserToGroup($user_id, $group_id): void {
    $pdo = openConn();

    $sql = "INSERT INTO user_group_link (group_id, user_id)
 VALUES (:group_id, :user_id)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'group_id' => $group_id,
        'user_id' => $user_id,
    ]);

    $pdo = null;
}

function generateUID() {
    $data = null;
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
}

function checkTimetableExists($user_id) {
    $pdo = openConn();

    $sql = "SELECT timetable_url
            FROM users
            WHERE id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    $pdo = null;

    if (isset($row['timetable_url'])) {
        return(true);
    }
    else {
        return(false);
    }
}

function createGroupLink($groupName): string{
    return ("https://web.cs.manchester.ac.uk/q98040ac/X3GroupProject/pages/invite.php?id=" . generateUID());
}

//createGroup("testGroup", null);