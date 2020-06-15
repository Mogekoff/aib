<html>
<head>
    <title>Чертач</title>
    <link id="favicon" rel="icon" href="img/favicon">
    <link rel="stylesheet" type="text/css" href="css/null.css">
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
    <meta name="description" content="Anonymous imageboard 'chertach'">
    <meta name="keywords" content="imageboard,anonymity">
    <meta name="author" content="anonym">
    <script src="js/funcs.js"></script>
    <?php
        require_once("include/funcs.php");
        $status = whereAmI();
    ?>
</head>
<body onload="postFormDisplay('<?php echo $status ?>', 'hide');">
<header>
    <?php
        $query = getBoard($_GET["board"]);
        $board = pg_fetch_array($query);
    ?>
    <div class="welcome">
        <img src="img/<?php echo $board["banner_uri"] ?>" width="200" height="100" >
        <h1><?php echo "/<a href='?board=".$board["uri"]."'>".$board["uri"]."</a>/ - ".$board["name"] ?></h1>
        <h2><?php echo $board["description"] ?></h2>
    </div>
    <hr>
    <div class="form-post">
        <a href="#" id="create-thread" onclick="postFormDisplay('<?php echo $status ?>');">Создать тред</a>
        <form class="input-post" id="input-post" style="display: none" enctype="multipart/form-data" method="post">
            <p>Текст поста:</p>
            <textarea name="text" cols="50" rows="8"></textarea>
            <p>Файл:</p>
            <input type="file" name="image" accept="image/*,image/jpeg">
            <br>
            <a href="#" onclick="document.getElementById('captcha').src = 'securimage/securimage_show.php?' + Math.random(); return false">
                <img id="captcha" src="securimage/securimage_show.php" alt="CAPTCHA Image" />
            </a>
            <br>
            <input type="text" name="captcha_code" size="20" maxlength="6" />
            <input type="submit" value="Отправить">
        </form>
    </div>
    <hr>
</header>
<section>
    <?php
        if($status == "board") {
            //Get array of all threads of, as example, /b/
            $query = getAllThreads($_GET["board"]);
            while($threads=pg_fetch_array($query)){
                //For every thread get 4 posts preview: one main as OP and three extra
                $main_post = true;
                $request = getThreadPreview($threads["id"]);
                while($posts = pg_fetch_array($request)) {
    ?>              <article class="<?php if($main_post) echo "main-post"; else echo "post" ?>">
                        <p class="header-post">
                            <?php
                                echo $board["anon_name"].' '.$posts["date"].' №'.$posts["id"];
                                if($main_post) {
                                    echo "<a href='null.php?board=".$_GET["board"]."&&thread=".$threads["id"]."'> Ответить</a>";
                                    $main_post=false;
                                }
                            ?>
                        </p>
                        <div class="pictures">
                            <?php
                                $quereq = getPictures($posts["id"]);
                                while($pictures=pg_fetch_array($quereq)) {

                            ?>
                                <a class="picture" href="img/<?php echo $pictures["uri"] ?>">
                                    <p><?php echo $pictures["uri"] ?></p>
                                    <img src="img/<?php echo $pictures["uri"] ?>" width="100" height="100" >
                                </a>

                            <?php
                                }
                            ?>
                        </div>
                        <div class="text">
                            <p><?php echo $posts["text"] ?></p>
                        </div>
                    </article>
    <?php
                }
            }
        }
        //Alternate printing posts if user inside thread
        else if($status == "thread") {
            $query = getAllPosts($_GET["thread"]);
            $main_post = true;
            for($i=1; $posts=pg_fetch_array($query); $i++) {
    ?>
                <article class="<?php if($main_post) echo "main-post"; else echo "post"; $main_post=false; ?>">
                    <p class="header-post">
                        <?php
                            echo $board["anon_name"].' '.$posts["date"].' №'.$posts["id"].' #'.$i;
                        ?>
                    </p>
                    <div class="pictures">
                        <?php
                        $request = getPictures($posts["id"]);
                        while($pictures=pg_fetch_array($request)) {
                            ?>
                            <a class="picture" href="img/<?php echo $pictures["uri"] ?>">
                                <p><?php echo $pictures["uri"] ?></p>
                                <img width="100" height="100" src="img/<?php echo $pictures["uri"] ?>"/>
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="text">
                        <p><?php echo $posts["text"] ?></p>
                    </div>
                </article>
    <?php
            }
        }
    ?>
</section>
</body>