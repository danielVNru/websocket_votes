<?php

include './config.php';
$db = new mysqli($db_h, $db_u, $db_p, $db);
$token = $_COOKIE['token'];

$query = $db->query("SELECT `admin` FROM  `users` WHERE `token` = '$token'");
$query = $query->fetch_assoc();

$admin = false;
if ($query) {
    if ($query['admin'] == 4) $admin = true;
} else {
    do {
        $token = randStr(50);
        $query = $db->query("SELECT `id` FROM `users` WHERE `token`='$token'");
    } while (mysqli_fetch_assoc($query));
    $db->query("INSERT INTO `users` (`token`, `admin`) VALUES ('$token', '2')");
    setcookie('token', $token);
}

$u_id = get_userId();

$query = $db->query("SELECT * FROM `now_voting` WHERE `id`='1'");
$now_voting = $query->fetch_assoc()['voting'];

$query = $db->query("SELECT * FROM `now_voting` WHERE `id`='2'");
$count_voting = $query->fetch_assoc()['voting'];

$query = $db->query("SELECT * FROM `votings` WHERE `id` = '$now_voting'");
$tracks = $query->fetch_assoc();

$data = [
    'track1' => infoForTrack($tracks['track1']),
    'track2' => infoForTrack($tracks['track2']),
    'track3' => infoForTrack($tracks['track3']),
    'votes1' => votes(1),
    'votes2' => votes(2),
    'votes3' => votes(3),
    'user_choise' => userChoise($u_id, $now_voting)
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


?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dj</title>
    <script src="https://daniel-vn.ru/granim/granim.min.js?_v=20220612214901"></script>
    <link rel="stylesheet" href="css/style.min.css?_v=20220612214901">
    <link rel="stylesheet" href="/css/dop.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800;900&display=swap&_v=20220612214901" rel="stylesheet">
    <style>
        .img1 {
            background-image: url(<?php echo $data['track1']['img'] ?>);
        }

        .img2 {
            background-image: url(<?php echo $data['track2']['img'] ?>);
        }

        .img3 {
            background-image: url(<?php echo $data['track3']['img'] ?>);
        }


        <?php if ($now_voting == 1) : ?>
        
        .img1 {
            background-image: url('/img/header/qrcode.svg');
        }

        .img2 {
            background-image: url('/img/header/gol.svg');
        }

        .img3 {
            background-image: url('/img/header/mus.svg');
        }

        <?php endif; ?>
    </style>
</head>

<body>
    <div class="wrapper">
        <header class="header">
            <div class="header__conteiner">
                <div class="logo">
                    <div class="logo__ico"></div>
                    <span class="logo__text">Dj</span>
                </div>
                <div class="btns">
                    <button class="btn qr">
                        <div class="qr__ico"></div>
                    </button>
                    <?php echo (!$admin ? '<input type="button" class="btn auth" value="Вход">' : '<div class="adm"> <div class="adm__ico"></div> Админ</div>') ?>
                </div>
            </div>
        </header>
        <section class="screen">
            <div class="screen__conteiner">
                <h1 class="screen__title">Голосование</h1>
                <div class="voting">
                    <?php if ($now_voting > 1 && $now_voting <= $count_voting+1) : ?>
                        <div anim="left_a" class="voting__optoin song song1 anim">
                            <div class="song__img img1"></div>
                            <h2 class="song__name"><?php echo $data['track1']['name'] ?></h2>
                            <p class="song__author"><?php echo $data['track1']['artist'] ?></p>

                            <?php
                            if ($data['user_choise'] == 0 && !$admin) : ?>
                                <input type="button" id="vote1" value="Проголосовать!" class="btn vote">
                            <?php endif;
                            if ($admin) : ?>
                                <div class="song__score song__score1 <?php if ($proc1 >= $proc2 && $proc1 >= $proc3) echo '--win' ?>"><?php echo $proc1 ?>%</div>
                            <?php endif; ?>
                            <?php echo (($data['user_choise'] != 0 && $data['user_choise'] == 1) ? '<div class="your-choise">Твой выбор!</div>' : '')  ?>
                            <?php echo (($data['user_choise'] != 0 && $data['user_choise'] != 1) ? '<div class="-empty"></div>' : '')  ?>


                        </div>
                        <div  anim="def_a" class="voting__optoin song song2 anim">
                            <div class="song__img img2"></div>
                            <h2 class="song__name"><?php echo $data['track2']['name'] ?></h2>
                            <p class="song__author"><?php echo $data['track2']['artist'] ?></p>
                            <?php
                            if ($data['user_choise'] == 0 && !$admin) : ?>
                                <input type="button" id="vote2" value="Проголосовать!" class="btn vote">
                            <?php endif;
                            if ($admin) : ?>
                                <div class="song__score song__score2 <?php if ($proc2 >= $proc1 && $proc2 >= $proc3) echo '--win' ?>"><?php echo $proc2 ?>%</div>
                            <?php endif; ?>
                            <?php echo (($data['user_choise'] != 0 && $data['user_choise'] == 2) ? '<div class="your-choise">Твой выбор!</div>' : '')  ?>
                            <?php echo (($data['user_choise'] != 0 && $data['user_choise'] != 2) ? '<div class="-empty"></div>' : '')  ?>
                        </div>
                        <div anim="right_a" class="voting__optoin song song3 anim">
                            <div class="song__img img3"></div>
                            <h2 class="song__name"><?php echo $data['track3']['name'] ?></h2>
                            <p class="song__author"><?php echo $data['track3']['artist'] ?></p>
                            <?php
                            if ($data['user_choise'] == 0 && !$admin) : ?>
                                <input type="button" id="vote3" value="Проголосовать!" class="btn vote">
                            <?php endif;
                            if ($admin) : ?>
                                <div class="song__score song__score3 <?php if ($proc3 >= $proc2 && $proc3 >= $proc1) echo '--win' ?>"><?php echo $proc3 ?>%</div>
                            <?php endif; ?>
                            <?php echo (($data['user_choise'] != 0 && $data['user_choise'] == 3) ? '<div class="your-choise">Твой выбор!</div>' : '')  ?>
                            <?php echo (($data['user_choise'] != 0 && $data['user_choise'] != 3) ? '<div class="-empty"></div>' : '')  ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($now_voting == 1) : ?>
                        <div anim="left_a" class="voting__optoin song song1 anim">
                            <div class="song__img img1"></div>
                            <h2 class="song__name">Отсканируй QR</h2>
                            <p class="song__author">И жди начала!</p>
                        </div>
                        <div anim="def_a" class="voting__optoin song song2 anim">
                            <div class="song__img img2"></div>
                            <h2 class="song__name">Голосуй</h2>
                            <p class="song__author">За понравившийся трек</p>
                        </div>
                        <div anim="right_a" class="voting__optoin song song3 anim">
                            <div class="song__img img3"></div>
                            <h2 class="song__name">Наслаждайся музыкой</h2>
                            <p class="song__author">Которую выбрали все вместе</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($now_voting == $count_voting+2) : ?>
                        <div anim="left_a" class="voting__optoin song song3 anim">
                            <div class="song__img img3 das"></div>
                            <h2 class="song__name">Даша</h2>
                            <p class="song__author">Вокалистка</p>
                            <div class="social">
                                <a href="https://vk.com/tikhomirova324"><div class="soc vk"></div></a>
                                <a href="https://t.me/tikhomirova0713"><div class="soc tg"></div></a>                                
                            </div>
                        </div>
                        <div anim="def_a" class="voting__optoin song song2 anim">
                            <div class="song__img img2 ana"></div>
                            <h2 class="song__name">Аня</h2>
                            <p class="song__author">воркинг над картинками</p>
                            <div class="social">
                                <a href="https://vk.com/club207944307"><div class="soc vk"></div></a>                         
                            </div>
                        </div>
                        <div anim="right_a" class="voting__optoin song song1 anim">
                            <div class="song__img img1 dan"></div>
                            <h2 class="song__name">Даник</h2>
                            <p class="song__author">WEB-разработчик</p>
                            <div class="social">
                                <a href="https://vk.com/kompromis_live"><div class="soc vk"></div></a>
                                <a href="https://t.me/DanielVNru"><div class="soc tg"></div></a>                                
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php if ($admin) : ?>
            <div class="navigation">
                <div class="navigation__conteiner">
                <?php if ($now_voting == 1) : ?>
                    <input type="button" value="Начать" class="btn go-modal">
                <?php endif; ?>
                <?php if ($now_voting > 1 && $now_voting <= $count_voting+1) : ?>
                    <input type="button" value="Завершить" class="btn go-modal">
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="modal --none qr-mod">
            <div class="modal__content">
                <input type="button" value="X" class="btn-close">
                <canvas id="cvs"></canvas>
                <picture>
                    <source srcset="/img/modal/qr2.png" type="image/webp" style="border-radius: 20px;"><img style="border-radius: 20px;" src="/img/modal/qr2.png" class="img-qr" title="QR код">
                </picture>
            </div>
        </div>
        <div class="modal auth-mod --none">
            <form class="modal__content" onsubmit="return false">
                <input type="button" value="X" class="btn-close">
                <div class="input">
                    <h3 class="input__title">Login</h3>
                    <input type="text" class="input__inp log" placeholder="admin">
                </div>
                <div class="input">
                    <h3 class="input__title">Password</h3>
                    <input type="password" class="input__inp pas" placeholder="qwerty">
                </div>
                <input type="submit" value="Войти" class="btn go">
            </form>
        </div>
        <div class="modal next-mod --none">
            <div class="modal__content" onsubmit="return false">
                <input type="button" value="X" class="btn-close">
                <div class="modal__title">Следующее голосование?</div>
                <input type="button" value="Начать" class="btn stop">
            </div>
        </div>
    </div>
    <script>
        <?php
        echo "let token = '" . $token . "'";
        ?>
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js?_v=20220612214901"></script>
    <script src="./js/app.min.js?_v=20220612214901"></script>
    <script src="./js/auth.js"></script>
    <?php if ($admin) : ?>
        <script src="./js/next.js"></script>
    <?php endif; ?>
    
    <script src="./js/vote.js"></script>
    <script src="./js/anim.js"></script>
    <script src="./js/modal.js"></script>
    <?php if (!$admin) : ?>
        <script src="./js/ws_user.js"></script>
    <?php endif; ?>
</body>

</html>



<?php
function get_userId()
{
    global $db;
    $token = $_COOKIE['token'];
    $query = $db->query("SELECT `id` FROM `users` WHERE `token`='$token'");
    return $query->fetch_assoc()['id'];
}

function votes(int $no)
{
    global $db, $now_voting;
    $query = $db->query("SELECT COUNT(*) FROM `u_votes` WHERE `no_voting` = '$now_voting' AND `vote` = '$no'");
    return $query->fetch_assoc()['COUNT(*)'];
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

?>