<?php
define("PORT", '8090');
require_once("./clsses/Chat.php");
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
$chat = new Chat();
$soket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($soket, SOL_SOCKET, SO_REUSEADDR, 1);

socket_bind($soket, 0, PORT);

socket_listen($soket);

$client_socket_array = array($soket);

while (true) {
    $new_socket_array = $client_socket_array;
    $nullA = [];

    socket_select($new_socket_array, $nullA, $nullA, 0, 10);


    if (in_array($soket, $new_socket_array)) {

        $new_socket = socket_accept($soket);
        $client_socket_array[] = $new_socket;
        $header = socket_read($new_socket, 1024);

        $chat->sendHeaders($header, $new_socket, 'dj', PORT);
        socket_getpeername($new_socket, $client_ip);

        // $connectionASK = $chat->newConnectionASK($client_ip);
        // $chat->send($connectionASK, $client_socket_array);

        $new_socket_array_index = array_search($soket, $new_socket_array);

        unset($new_socket_array[$new_socket_array_index]);
    }

    foreach ($new_socket_array as $new_socket_array_res) {

        while (socket_recv($new_socket_array_res, $socket_data, 1024, 0) >= 1) {
            $socket_message = $chat->unseal($socket_data);

            $mess_obj = json_decode($socket_message);

            ['data' => $send_data, 'is_send' => $is_send] = $chat->command($mess_obj->token, $mess_obj->t);
            if ($is_send) $chat->send($chat->seal(json_encode($send_data)), $client_socket_array);

            break 2;
        }

        $socket_data = @socket_read($new_socket_array_res, 1024, PHP_NORMAL_READ);

        if ($socket_data === false) {
            // socket_getpeername($new_socket_array_res, $client_ip);
            // $disconnect = $chat->newDisconnect($client_ip);
            // $chat->send($disconnect, $client_socket_array);

            $new_socket_array_index = array_search($new_socket_array_res, $client_socket_array);

            unset($client_socket_array[$new_socket_array_index]);
        }
    }
}

socket_close($soket);
