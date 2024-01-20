<?php
header('Content-type: applicaton/json');
// header('Access-Control-Allow-Origin: *');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// error_reporting(0);
$route = $_GET['route'];
include '../config.php';
$db = new mysqli($db_h, $db_u, $db_p, $db);




if ($route == 'auth') {
    $log = $_POST['login'];
    $pass = $_POST['pass'];

    if ($log == 'admin' && $pass == '123321') {
        header("HTTP/1.1 200 OK");
        json_die([
            'status' => 'ok',
            'token' => 'AxAxAxAXAXxaxaxAXhuyaus'
        ]);
    } else {
        header("HTTP/1.1 403 Forbidden");
        json_die([
            'status' => 'not ok',
            'log' => 'Логин или пароль не верный!',
            'pas' => 'Логин или пароль не верный!',
        ]);
    }
} else if ($route == 'next') {
    isAdmin();

    $query = $db->query("SELECT `voting` FROM `now_voting`");
    $new_voting = $query->fetch_assoc()['voting'] + 1;
    $db->query("UPDATE `now_voting` SET `voting`='$new_voting' WHERE `id`='1'");

    header("HTTP/1.1 200 OK");
    json_die([
        'status' => $new_voting
    ]);
} else if ($route == 'back') {
    isAdmin();

    $query = $db->query("SELECT `voting` FROM `now_voting`");
    $new_voting = $query->fetch_assoc()['voting'] - 1;
    $db->query("UPDATE `now_voting` SET `voting`='$new_voting'");

    header("HTTP/1.1 200 OK");
    json_die([
        'status' => $new_voting
    ]);
} else if ($route == 'vote') {
    auth();
    $u_id = get_userId();
    $errs = [];

    $query = $db->query("SELECT * FROM `now_voting` WHERE `id`='1'");
    $now_voting = $query->fetch_assoc()['voting'];


    $vote = $_POST['vote'];
    if ($vote != 1 && $vote != 2 && $vote != 3) $errs['vote'] = "Не существующий вариант ответа!";

    $query = $db->query("SELECT `vote` FROM `u_votes` WHERE `no_voting`='$now_voting' AND `user`='$u_id'");
    if ($query->fetch_assoc()) $errs['vote'] = "Уже проголосовал!";

    if ($errs) {
        header("HTTP/1.1 403 Forbidden");
        json_die($errs);
    } else {
        $db->query("INSERT INTO `u_votes` (`no_voting`,`user`,`vote`) values ('$now_voting','$u_id','$vote')");
        header("HTTP/1.1 200 OK");
        json_die([
            'status' => 'ok',
            'vote'=>$vote
        ]);
    }
} else if ($route == 'now') {
    auth();
    $u_id = get_userId();

    $query = $db->query("SELECT * FROM `now_voting` WHERE `id`='1'");
    $now_voting = $query->fetch_assoc()['voting'];

    $query = $db->query("SELECT * FROM `votings` WHERE `id` = '$now_voting'");
    $tracks = $query->fetch_assoc();

    $data = [
        'track1' => infoForTrack($tracks['track1']),
        'track2' => infoForTrack($tracks['track2']),
        'track3' => infoForTrack($tracks['track3']),
        'user_choise' => userChoise($u_id, $now_voting)
    ];

    json_die($data);
}

function randChar()
{
    $permitted_chars = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    $rand = substr(str_shuffle($permitted_chars), 0, 1);
    return $rand;
}

function randStr(int $num = 15)
{
    $str = '';
    for ($i = 1; $i <= $num; $i++) {
        $str = $str . randChar();
    }
    return $str;
}

function json_die($val)
{
    die(json_encode($val, JSON_UNESCAPED_UNICODE));
}

function get_userId()
{
    global $db;
    $token = $_COOKIE['token'];
    $query = $db->query("SELECT `id` FROM `users` WHERE `token`='$token'");
    return $query->fetch_assoc()['id'];
}

function auth()
{
    global $db;
    $token = $_COOKIE['token'];
    $query = $db->query("SELECT `id` FROM `users` WHERE `token`='$token'");
    if (!mysqli_fetch_assoc($query)) {
        header("HTTP/1.1 403 Forbidden");
        json_die(['auth' => 'Не авторизирован!']);
    }
}

function infoForTrack($track_id)
{
    global $db;
    $query = $db->query("SELECT * FROM `tracks` WHERE `id`='$track_id'");
    return $query->fetch_assoc();
}

function userChoise($u_id, $now_voting)
{
    global $db;
    $query = $db->query("SELECT `vote` FROM `u_votes` WHERE `no_voting`='$now_voting' AND `user`='$u_id'");
    return $query->fetch_assoc()['vote'] ?? 0;
}

function isAdmin()
{
    global $db;
    $token = $_COOKIE['token'];
    $query = $db->query("SELECT `admin` FROM `users` WHERE `token`='$token'");
    if (!mysqli_fetch_assoc($query)) {
        header("HTTP/1.1 403 Forbidden");
        json_die(['auth' => 'Нет прав доступа!']);
    }
}
