<?php
include '../config.php';
$db = new mysqli($db_h, $db_u, $db_p, $db);

class Chat
{
    public function sendHeaders($headersTXT, $new_socket, $host, $port)
    {
        $headers = [];
        // echo 1234;

        $tmp_line = preg_split('/\r\n/', $headersTXT);

        foreach ($tmp_line as $line) {
            $line = trim($line);

            if (preg_match('/\A(\S+): (.*)\z/', $line, $mathes)) {
                $headers[$mathes[1]] = $mathes[2];
            }
        }

        $key = $headers['Sec-WebSocket-Key'];
        $s_key = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        $header = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "WebSocket-Origin: $host\r\n"
            . "WebSocket-Location: ws://$host:$port/server/index.php\r\n"
            . "Sec-WebSocket-Accept: $s_key\r\n\r\n";
        socket_write($new_socket, $header, strlen($header));
    }

    public function newConnectionASK($client_ip)
    {
        $message = "Пользователь " . $client_ip . " вошёл в чат!";
        $messageArray = ['mess' => $message, 'type' => 'teh_mess'];

        $ask = $this->seal(json_encode($messageArray));
        return $ask;
    }

    public function newDisconnect($client_ip)
    {
        $message = "Пользователь " . $client_ip . " вышел из чата!";
        $messageArray = ['mess' => $message, 'type' => 'teh_mess'];

        $ask = $this->seal(json_encode($messageArray));
        return $ask;
    }


    public function seal($soket_data)
    {
        $b1 = 0x81;
        $lenght = strlen($soket_data);
        $header = '';

        if ($lenght <= 125) {
            $header = pack('CC', $b1, $lenght);
        } elseif ($lenght > 125 && $lenght < 65536) {
            $header = pack('CCn', $b1, 126, $lenght);
        } elseif ($lenght > 65536) {
            $header = pack('CCNN', $b1, 127, $lenght);
        }

        return $header . $soket_data;
    }

    public function unseal($soket_data)
    {

        $lenght = ord($soket_data[1]) & 127;

        if ($lenght == 126) {
            $mask = substr($soket_data, 4, 4);
            $data = substr($soket_data, 8);
        } elseif ($lenght == 127) {
            $mask = substr($soket_data, 10, 4);
            $data = substr($soket_data, 14);
        } else {
            $mask = substr($soket_data, 2, 4);
            $data = substr($soket_data, 6);
        }

        $socket_str = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $socket_str .= $data[$i] ^ $mask[$i % 4];
        }

        return $socket_str;
    }

    public function send($message, $client_socket_array)
    {
        $mess_lenght = strlen($message);
        // echo $message;
        foreach ($client_socket_array as $client_socket) {
            // echo $client_socket;
            @socket_write($client_socket, $message, $mess_lenght);
        }
        return true;
    }

    public function createMess($user_name, $mess_str)
    {
        $messArr = [
            'username' => $user_name,
            'mess' => $mess_str,
            'type' => 'message'
        ];

        return self::seal(json_encode($messArr));
    }

    public function command($token, $command)
    {



        // echo "$token: $command";

        if ($command == "next") {
            // echo 'next';
            global $db;
            // print_r(['token'=>$token]);
            $query = $db->query("SELECT `admin` FROM `users` WHERE `token`='$token'");
            if (mysqli_fetch_assoc($query)['admin'] != 4) return ['data' => '', 'is_send' => false];

            $query = $db->query("SELECT * FROM `now_voting` WHERE `id`='1'");
            $now_voting = $query->fetch_assoc()['voting'];

            $query = $db->query("SELECT * FROM `now_voting` WHERE `id`='2'");
            $count_voting = $query->fetch_assoc()['voting'];

            if($now_voting < $count_voting+2){
                $query = $db->query("SELECT * FROM `votings` WHERE `id` = '$now_voting'");
                $tracks = $query->fetch_assoc();

                $data = [
                    'track1' => infoForTrack($tracks['track1']),
                    'track2' => infoForTrack($tracks['track2']),
                    'track3' => infoForTrack($tracks['track3']),
                    'type' => 'next',
                    'is_end' => false
                ];
            }
            else {
                $data = [
                    'type' => 'next',
                    'is_end' => true
                ];
            }
            

            
            return ['data' => $data, 'is_send' => true];
        } elseif ($command == "vote") {
            // echo 'vote';
            $data = [
                'votes1' => votes(1),
                'votes2' => votes(2),
                'votes3' => votes(3),
                'type' => 'vote'
            ];

            $proc1 = 0;
            $proc2 = 0;
            $proc3 = 0;

            if ($data['votes1'] != 0 || $data['votes2'] != 0 || $data['votes3'] != 0) {

                $proc1 = 100 / ($data['votes1'] + $data['votes2'] + $data['votes3']) * $data['votes1'];
                $proc1 = round($proc1, 2);
                $proc2 = 100 / ($data['votes1'] + $data['votes2'] + $data['votes3']) * $data['votes2'];
                $proc2 = round($proc2, 2);
                $proc3 = 100 / ($data['votes1'] + $data['votes2'] + $data['votes3']) * $data['votes3'];
                $proc3 = round($proc3, 2);
            }
            $data['votes1'] = $proc1;
            $data['votes2'] = $proc2;
            $data['votes3'] = $proc3;
            return ['data' => $data, 'is_send' => true];
        } else {
            // echo 'new';
        }

        return ['data' => '', 'is_send' => false];
    }
}

function infoForTrack($track_id)
{
    global $db;
    $query = $db->query("SELECT * FROM `tracks` WHERE `id`='$track_id'");
    return $query->fetch_assoc();
}

function votes(int $no)
{
    global $db;
    $query = $db->query("SELECT * FROM `now_voting` WHERE `id`='1'");
    $now_voting = $query->fetch_assoc()['voting'];
    $query = $db->query("SELECT COUNT(*) FROM `u_votes` WHERE `no_voting` = '$now_voting' AND `vote` = '$no'");
    return $query->fetch_assoc()['COUNT(*)'];
}
