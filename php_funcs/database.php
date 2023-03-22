<?php

use JetBrains\PhpStorm\NoReturn;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once "../PHPMailer-master/src/PHPMailer.php";
require_once "../PHPMailer-master/src/SMTP.php";
require_once "../PHPMailer-master/src/Exception.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "logging.php";
date_default_timezone_set('GMT');

function openConn(): PDO {
    if (file_exists('../config.inc.php')) {
        require '../config.inc.php';
    } else {
        require './config.inc.php';
    }


    try
    {
        $pdo = new PDO("mysql:host=$database_host;dbname=2022_comp10120_x3", $database_user, $database_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_WARNING);
    }
    catch (PDOException $pe)
    {
        die("Could not connect to $database_host :" . $pe->getMessage());
    }
    return $pdo;
}

#[NoReturn] function errorRedirect($error_message): void
{
    header('Location: ' . "./error.php?error=" . $error_message);
    die();
}

function addTimetable($id, $timetable_url): void
{
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
function createUser($name, $profile_picture, $username, $email, $password): void
{
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

    $pdo = null;
    if (isset($row['email'])) {
        return(true);
    }
    else {
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

    $pdo = null;
    if (isset($row['username'])) {
        return(true);
    }
    else {
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
    $pdo = null;
    if (isset($row['password_hash'])) {
        if (password_verify($password, $row['password_hash'])) {
            return ($row['id']);
        }
        else {
            return (-1);
        }
    }
    else{
        return(-1);
    }
}


function getTimetable($url, $user_id): bool|array
{
    $fileContent = file_get_contents($url);
    if (!$fileContent) {
        return(false);
    }
    if(str_starts_with($fileContent, "BEGIN:VCALENDAR")) {
        $events = parseTimetable($fileContent, $user_id);
        return($events);
    }
    else {
        return(false);
    }
}

function parseTimetable($fileContent, $user_id) :array {
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
    $profileStarts = [];
    $profileEnds = [];


    $offset=0;
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
        $summary[] = remove_emoji(str_replace('\,',',',substr($current, $pos, $pos + strpos($line, PHP_EOL) - (strpos($current, "SUMMARY:") + 8))));

        $pos = strpos(substr($current, strpos($current, "DTSTART") + 7), ":") + 1 + strpos($current, "DTSTART") + 7;
        $line = substr($current, $pos);
        $eventStartTime = substr($current, $pos, $pos + strpos($line, PHP_EOL) - $pos);
        $dt_starts[] = substr($eventStartTime, 0, 4) . '-' . substr($eventStartTime, 4, 2) . '-' . substr($eventStartTime, 6, 2) . ' ' . substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";
        $profileStarts[] = substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";

        $pos = strpos(substr($current, strpos($current, "DTEND") + 5), ":") + 1 + strpos($current, "DTEND") + 5;
        $line = substr($current, $pos);
        $eventStartTime = substr($current, $pos, $pos + strpos($line, PHP_EOL) - $pos);
        $dt_ends[] = substr($eventStartTime, 0, 4) . '-' . substr($eventStartTime, 4, 2) . '-' . substr($eventStartTime, 6, 2) . ' ' . substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";
        $profileEnds[] = substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";

        $AllEvents[] = [$summary[$i],$dt_starts[$i], $dt_ends[$i],1]; //adds all current event information (summary,start,end) to AllEvents array.
    }
    $profileStart = min($profileStarts);
    $profileEnd = max($profileEnds);
    $pdo = openConn();

    $sql = "UPDATE users 
            SET profileStart = :profileStart, profileEnd = :profileEnd
            WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'profileStart' => $profileStart,
        'profileEnd' => $profileEnd,
        'user_id' => $user_id,
    ]);

    $pdo = null;

    return($AllEvents);
}

function remove_emoji($string): array|string|null
{
    $string = str_replace('\n','', $string);
    // Match Enclosed Alphanumeric Supplement
    $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
    $clear_string = preg_replace($regex_alphanumeric, '', $string);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clear_string = preg_replace($regex_symbols, '', $clear_string);

    // Match Emoticons
    $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clear_string = preg_replace($regex_emoticons, '', $clear_string);

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clear_string = preg_replace($regex_transport, '', $clear_string);

    // Match Supplemental Symbols and Pictographs
    $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
    $clear_string = preg_replace($regex_supplemental, '', $clear_string);

    // Match Miscellaneous Symbols
    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $clear_string = preg_replace($regex_misc, '', $clear_string);

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    return preg_replace($regex_dingbats, '', $clear_string);
}

function saveTimetable($user_id, $events): void
{
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
            'active' => $event[3],
            'summary' => $event[0],
            'dt_start' => $event[1],
            'dt_end' => $event[2]
        ]);
        if (!$result) {
            $fails++;
            $errors[] = implode(error_get_last());
        }
    }

    if ($fails == 0) { // Set last_updated_date to the current time, or log if failed adding events.
        $sql = "UPDATE users SET timetable_last_updated='" . date('Y-m-d H:i:s') . "' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $user_id,
        ]);
    } else {
        doLog("ERROR", "Adding events to db failed.", implode($errors), "background.php", $user_id);
    }

    $pdo = null;
}

function createGroup($name, $user_id): bool|string
{
    $groupUID = generateUID();
    $name = trim($name); //Remove leading and trailing spaces
    $pdo = openConn();

    // Check if the user already has a group with the same name. If so, error.
    $sql = "SELECT `groups`.`id`
            FROM `groups` INNER JOIN `user_group_link` ON `groups`.`id` = `user_group_link`.`group_id`
            WHERE `groups`.name = :name AND `user_group_link`.`user_id` = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'user_id' => $user_id
    ]);

    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (isset($row["id"])) {
        return false;
    }

    $sql = "INSERT INTO `groups` (name, groupUID)
 VALUES (:name, :groupUID)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'groupUID' => $groupUID
    ]);

    addUserToGroup($user_id, $pdo->lastInsertId());
    $pdo = null;
    return dirname($_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI']) . "/invite.php?id=" . $groupUID;
}
function getGroupInfoFromInviteLink($groupUID): bool|array
{
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
    while(true) {
        $data = null;
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        $groupUID = vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));

        // Output the 36 character UUID.
        if(!doesUIDExist($groupUID)){
            break;
        }
    }
    return($groupUID);
}

function doesUIDExist($groupUID): bool
{
    $pdo = openConn();
    $sql = "SELECT groupUID
            FROM `groups`
            WHERE groupUID = :groupUID";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'groupUID' => $groupUID
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();
    $pdo = null;
    if (isset($row['groupUID'])) {
        return(true);
    }
    else {
        return(false);
    }
}

function checkTimetableExists($user_id): bool
{
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

function getGroupUsers($group_id): array
{
    $pdo = openConn();

    $sql = "SELECT user_id
            FROM user_group_link
            WHERE group_id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $users = [];
    while ($row = $stmt->fetch()){
        $users[] = $row['user_id'];
    }
    $pdo = null;
    return($users);
}

function getUserEvents($user_id): bool|array
{
    $pdo = openConn();

    $sql = "SELECT IF(active, 'rgb(49, 95, 211)', 'rgb(200, 30, 65)') as color, id, summary as title, DATE_FORMAT(dt_start, '%Y-%m-%dT%H:%i:%sZ') as start, DATE_FORMAT(dt_end, '%Y-%m-%dT%H:%i:%sZ') as 'end'
            FROM events
            WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $events = [];
    while ($row = $stmt->fetch()){
        $events[] = $row;
    }
    $pdo = null;
    if (empty($events)) {
        return(false);
    }
    else {
        return($events);
    }
}

function getGroupEvents($group_id): bool|array
{
    $pdo = openConn();
    $sql = "SELECT DATE_FORMAT(dt_start, '%Y-%m-%dT%H:%i:%s') as start, DATE_FORMAT(dt_end, '%Y-%m-%dT%H:%i:%s') as 'end'
            FROM (events INNER JOIN user_group_link ON events.user_id = user_group_link.user_id) 
            WHERE user_group_link.group_id=:group_id AND events.active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id //replaces :group_id in sql statement with $group_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC); //who knows
    $events = [];
    while ($row = $stmt->fetch()){
        $events[] = $row;
    }
    $pdo = null;
    if (empty($events)) {
        return(false);
    }
    else {
        return($events);
    }
}

function getBestOfficeHours($AllUsers): array{
    $pdo = openConn();

    $sql = "SELECT office_begin, office_end
            FROM users
            WHERE id IN(".implode(',',$AllUsers).")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $startTime = 0;
    $endTime = 23;
    while ($row = $stmt->fetch()){
        if(!(is_null($row['office_begin']) || is_null($row['office_end']))) {
            $currentStart = intval(substr($row['office_begin'], 11, 2));
            $currentEnd = intval(substr($row['office_end'], 11, 2));
            if($currentEnd == 0){
                $currentEnd = 23;
            }
            if ($currentStart > $startTime) {
                $startTime = $currentStart;
            }
            if ($currentEnd < $endTime) {
                $endTime = $currentEnd;
            }
        }
    }
    $pdo = null;
    return([$startTime,$endTime]);
}

function getUserOfficeHours($id): array{
        $pdo = openConn();

        $sql = "SELECT office_begin, office_end, profileStart, profileEnd
            FROM users
            WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $row = $stmt->fetch();

        if (empty($row["profileStart"]) || empty($row["profileEnd"])) {
            $startTime = 0;
            $endTime = 23;
        } else {
            $startTime = intval(substr($row['profileStart'], 0, 2));
            $endTime = intval(substr($row['profileEnd'], 0, 2));
        }

        if (empty($row["profileStart"]) || empty($row["profileEnd"])) {
            $startOffice = 0;
            $endOffice = 23;
        } else {
            $startOffice = intval(substr($row['office_begin'], 11, 2));
            $endOffice = intval(substr($row['office_end'], 11, 2));
        }

        $pdo = null;

        return [$startTime,$endTime, $startOffice, $endOffice];
}

function whatTime($group_id): array
{
    $events = getGroupEvents($group_id);
    $times = [];

    foreach ($events as $event) {
        $times[] = $event['start'] . "s";
        $times[] = $event['end'] . "e";
    }
    sort($times);

    $unavailableTimes = [];
    for($i=0;$i<count($times);$i++){
        for($j = $i+1;$j<count($times) - 1;$j++){
            if(str_ends_with($times[$j], 'e')){
                if(str_ends_with($times[$j + 1], 's')){
                    if (substr($times[$j], 0, 19) != substr($times[$j+1], 0, 19)){
                        $unavailableTimes[] = ["title" => 'UNAVAILABLE', "start" => substr($times[$i], 0, 19) . "Z", "end" =>  substr($times[$j], 0, 19) . "Z"];
                        break;
                    }
                }
            }
        }
        $i = $j;
    }
    return($unavailableTimes);
}

function preserveInactiveEvents($user_id, $newEvents): array{
    $pdo = openConn();

    $sql = "SELECT summary, dt_start, dt_end
            FROM events
            WHERE user_id = :user_id AND active = 0";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $savedEvents = [];
    if(empty($row)){
        return($newEvents);
    }



    foreach($newEvents as $newEvent){
        $flag = true;
        foreach($row as $cRow){
            if($cRow['summary'] == $newEvent[0]){
                if($cRow['dt_start'] == $newEvent[1]){
                    if($cRow['dt_end'] == $newEvent[2]){
                        $savedEvents[] = [$newEvent[0],$newEvent[1],$newEvent[2],0];
                        $flag = false;
                        break;
                    }
                }
            }
        }
        if($flag) {
            $savedEvents[] = [$newEvent[0], $newEvent[1], $newEvent[2], 1];
        }
    }
    return($savedEvents);

}

function updateTimetable($user_id): bool
{
    $pdo = openConn();

    $sql = "SELECT timetable_url, timetable_last_updated
            FROM users
            WHERE id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    $pdo = null;

    if (!isset($row['timetable_url'])) {
        return false;
    }

    if (isset($row['timetable_last_updated'])) {
        $last_updated = new DateTime($row['timetable_last_updated']);
        $now = new DateTime();
        $diff = $now->diff($last_updated);
        if ($diff->days < 1) {
            return true;
        }
    }

    $events = getTimetable($row['timetable_url'], $user_id);
    if (!$events) {
        return false;
    }
    saveTimetable($user_id, preserveInactiveEvents($user_id, $events));

    return true;
}


function getUserGroupInfo($user_id): array
{
    $pdo = openConn();

    $sql = "SELECT groups.id, groups.name, groups.group_picture, groups.groupUID
            FROM `groups`  
            INNER JOIN `user_group_link` ON `groups`.`id` = `user_group_link`.`group_id`
            WHERE `user_group_link`.`user_id` = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $groupInfo = [];
    while ($row = $stmt->fetch()){
        $groupInfo[] = $row;
    }
    $pdo = null;
    return($groupInfo);
}

function makeEventInactiveAPI($event_id, $user_id): void
{
    $pdo = openConn();

    $sql = "UPDATE events
            SET active = !active
            WHERE id = :event_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'event_id' => $event_id,
        'user_id' => $user_id
    ]);
    $pdo = null;
}

function getUserInfo($user_id) {
    $pdo = openConn();

    $sql = "SELECT id, name, username, profile_picture, email, timetable_url, DATE_FORMAT(office_begin, '%H:%i') as office_begin, DATE_FORMAT(office_end, '%H:%i') as office_end
            FROM users
            WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $pdo = null;
    $row = $stmt->fetch();

    return($row);
}

function deleteGroupAPI($group_id, $user_id): bool
{
    $pdo = openConn();

    $sql = "SELECT group_id
            FROM user_group_link
            WHERE group_id = :group_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
        'user_id' => $user_id,
    ]);

    $row = $stmt->fetch();
    if (empty($row)) {
        return false;
    }

    $sql = "DELETE 
            FROM `groups`
            WHERE id = :group_id;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
    ]);
    $pdo = null;
    return true;
}

function changeNameAPI($group_id, $name, $user_id): bool
{
    $name = trim($name); //Remove leading and trailing spaces
    $pdo = openConn();

    // Check if the user already has a group with the same name. If so, error.
    $sql = "SELECT `groups`.`id`
            FROM `groups` INNER JOIN `user_group_link` ON `groups`.`id` = `user_group_link`.`group_id`
            WHERE `groups`.name = :name AND `user_group_link`.`user_id` = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'user_id' => $user_id
    ]);

    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (isset($row["id"])) {
        return false;
    }

    $sql = "SELECT group_id
            FROM user_group_link
            WHERE group_id = :group_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
        'user_id' => $user_id,
    ]);

    $row = $stmt->fetch();
    if (empty($row)) {
        return false;
    }

    $sql = "UPDATE `groups`
            SET `name` = :name
            WHERE id = :group_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'group_id' => $group_id
    ]);
    return true;
}

function deleteMemberAPI($group_id, $member_id, $user_id): bool
{
    $pdo = openConn();

    // Check if user has permissions for this group
    $sql = "SELECT group_id
            FROM user_group_link
            WHERE group_id = :group_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
        'user_id' => $user_id,
    ]);

    $row = $stmt->fetch();
    if (empty($row)) {
        return false;
    }

    $sql = "DELETE
            FROM `user_group_link`
            WHERE user_id = :member_id AND group_id= :group_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'member_id' => $member_id,
        'group_id' => $group_id
    ]);
    return true;
}

function getGroupEmailsAndName($group_id)
{
    $pdo = openConn();

    $sql = "SELECT `name`
            FROM `groups`
            WHERE `id` = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
    ]);
    $row = $stmt->fetch();

    $GroupName = $row['name'];

    $sql = "SELECT user_id
            FROM user_group_link
            WHERE group_id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $user_ids = [];
    while ($row = $stmt->fetch()){
        $user_ids[] = $row['user_id'];
    }

    $sql = "SELECT email
            FROM users
            WHERE id IN (" . implode(",", array_map('intval', $user_ids)) . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $userEmails = [];
    while ($row = $stmt->fetch()){
        $userEmails[] = $row['email'];
    }
    return([$GroupName, $userEmails]);
}

function createEventAPI($summary, $start, $end, $group_id, $user_id)
{
    $pdo = openConn();

    $sql = "SELECT group_id
            FROM user_group_link
            WHERE group_id = :group_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'group_id' => $group_id,
        'user_id' => $user_id,
    ]);
    $row = $stmt->fetch();
    if (empty($row)) {
        return false; // Check if user has permissions for this group
    }
    $ics = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\nBEGIN:VEVENT\nDTSTART:" . date("Ymd\THis\Z", strtotime($start)) . "\nDTEND:" . date("Ymd\THis\Z", strtotime($end)) . "\nLOCATION:" . "group meeting point" . "\nTRANSP: OPAQUE\nSEQUENCE:0\nUID:\nDTSTAMP:" . date("Ymd\THis\Z") . "\nSUMMARY:" . $summary . "\nDESCRIPTION:" . "This is a group meeting created by WhatTime" . "\nPRIORITY:1\nCLASS:PUBLIC\nBEGIN:VALARM\nTRIGGER:-PT10080M\nACTION:DISPLAY\nDESCRIPTION:Reminder\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n";
    $groupInfo = getGroupEmailsAndName($group_id);

    sendEmail($groupInfo[1], $ics, $summary, $groupInfo[0]);

}

function sendEmail($to, $ics_file, $title, $group_name) {
    $pdo = openConn();

    $sql = "SELECT smtp_pass
            FROM credentials";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $smtp_pass = $stmt->fetch()["smtp_pass"];

    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";

    $mail->SMTPDebug  = 1;
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    $mail->Host       = "smtp.gmail.com";
    $mail->Username   = "whattime.uom@gmail.com";
    $mail->Password   = $smtp_pass;

    $mail->IsHTML(true);

    foreach ($to as $email) {
        $mail->AddAddress($email);
    }

    $mail->SetFrom("whattime.uom@gmail.com", "WhatTime");
    $mail->Subject = "Invite to Group Meeting: " . $title . " in " . $group_name;
    $content = "Dear members,<br><br>You have been invited to a group meeting. Please find the attached ICS file to add the event to your calendar.<br><br>Best Regards<br>WhatTime Team";
    $mail->addStringAttachment($ics_file, 'invite.ics');

    $mail->MsgHTML($content);
    if(!$mail->Send()) {
        doLog("ERROR", "Error while sending Email.", var_export($mail, true), "sendEmail()");
    }
}