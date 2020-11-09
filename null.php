<html>
<head>
    <title>Чертач</title>
    <link id="favicon" rel="icon" href="/img/favicon">
    <link rel="stylesheet" type="text/css" href="/css/null.css">
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
    <meta name="description" content="Anonymous imageboard 'chertach'">
    <meta name="keywords" content="imageboard,anonymity">
    <meta name="author" content="anonym">
    <a href="#" title="GOTOP" class="goTop">
      <img src="/img/up-arrow.svg" width="30" height="30" >
    </a>
    <script src="/js/funcs11.js"></script>
    <?php
        session_start();
        include_once $_SERVER['DOCUMENT_ROOT'] . '/securimage/securimage.php';
        $securimage = new Securimage();
        require_once("include/funcs.php");

        //Where is user now
        $status = whereAmI();

        if (isset($_POST['captcha_code']) && $securimage->check($_POST['captcha_code']) == true && isset($_COOKIE['session_id'])) {

            if($status=='board')
                createThread($board["id"]);
            else if ($status =='thread')
                sendPost($_GET["thread"]);
        }
    ?>
</head>
<body onload="postFormDisplay('<?php echo $status ?>', 'hide');">
<div class="links">
    <a href="/">Главная</a> |
    <a href="/b">b</a> |
    <a href="/x">x</a> |
    <a href="/int">int</a>
</div>

<header>

    <div class="welcome">
        <img src="/img/<?php echo $board["banner_uri"] ?>" width="200" height="100" >
        <h1><?php echo "/<a href='/".$board["uri"]."'>".$board["uri"]."</a>/ - ".$board["name"] ?></h1>
        <h2><?php echo $board["description"] ?></h2>
    </div>
    <hr>
    <div class="form-post">
        <a href="#" id="create-thread" onclick="postFormDisplay('<?php echo $status ?>');">Создать тред</a>
        <form class="input-post" id="input-post" style="display: none" enctype="multipart/form-data" method="post">
            OP
            <input type="checkbox" name="op" value="true">
            Sage
            <input type="checkbox" name="sage" value="true">
            <p>Текст поста:</p>
            <textarea name="text" wrap="soft" cols="50" rows="8"></textarea>
            <p>Файл:</p>
            <input type="file" name="file[]" multiple accept="audio/ogg,audio/mp3,image/jpeg,image/jpg,image/png,image/gif,video/mp4,video/webm">
            <br>
            <a href="#" onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false">
                <img id="captcha" src="/securimage/securimage_show.php" alt="CAPTCHA Image" />
            </a>
            <br>
            <input type="text" name="captcha_code" size="15" maxlength="3" />
            <input type="submit" value="Отправить">
        </form>
    </div>
    <hr>
</header>
<section>
    <?php
        if($status == "board")
            parseThreads();
        //Alternate printing posts if user inside thread
        else if($status == "thread")
            parsePosts();
    ?>
</section>

<div class="updater">
    <div class="autoupdate">
        <p>Автообновление:</p>
        <input id="autoupdate" onclick='updater()' type="checkbox" name="autoupdate" value="true">
    </div>
    <div class="update">
        <a href='#' onclick='document.location.reload(true);'>Обновить</a>
    </div>
    <div class="timer">
        <p id="timer">14</p>
    </div>
</div>
<script>if(document.cookie.split(';').filter((item) => item.includes('updater=true')).length) {
    document.getElementById("autoupdate").checked = true;
    updater();
}
</script>
</body>