<?php
//Never expiring date for cookies
const NEVEREXPIRING = 10 * 365 * 24 * 60 * 60;  //10 years
const UPLOADDIR = '/var/www/imageboard/img/upload/';
const MAXFILESIZE = 16 * 1024 * 1024; //5MB
const SHORTTEXT = 666;
const FFMPEG = "/var/www/imageboard/bin/ffmpeg"; //ffmpeg dir
//Database Connection
$dbconn = pg_connect("host=db dbname=postgres user=postgres password=ldw;qodk2");
//Identify anon and set cookie
$my_id = getAnonID();

//Get all posts by thread
function getAllPosts($thread_id) {
    getSafeID($thread_id);
    $query = pg_query("SELECT * FROM posts WHERE hidden = FALSE AND thread_id=$thread_id ORDER BY id ASC");
    
    //If no such thread then return false
    if(pg_num_rows($query)==0) return error();

    return $query;
}

//Get all threads by board
function getAllThreads($board_uri) {
    //Get safe input of board uri
    $board_uri = getSafeText($board_uri);
    //Ask for id of board
    $query = pg_query("SELECT id FROM boards WHERE uri='$board_uri'");
    //If no such board then return false
    if(pg_num_rows($query)==0) return error();
    //Else get board id and ask for all threads for this board
    $board_id = pg_fetch_row($query)[0];
    return pg_query("SELECT thread_id FROM posts WHERE hidden = FALSE AND id in (SELECT DISTINCT ON (thread_id) id FROM 
                    posts 
                    WHERE thread_id in (SELECT id FROM threads WHERE board_id=$board_id) ORDER BY thread_id DESC) ORDER BY id DESC");
}

//Get all boards
function getAllBoards() {
    return pg_query("SELECT * FROM boards WHERE hidden = FALSE ORDER BY id");
}

//Send post to thread
function sendPost($thread_id) {
    getSafeID($thread_id);
    $text = getSafeText($_POST["text"]);

    if(strlen($text)==0 && !isset($_FILES['file']['name'])) return error();

    $text=replaceTags($text);

    $anon_id = getAnonID();
    if(!isset($_POST["op"]))
        $op = "false";
    else $op = $_POST["op"];
    if(!isset($_POST["sage"]))
        $sage = "false";
    else $sage = $_POST["sage"];

    if($op == "true" && pg_fetch_row(pg_query("SELECT op_id FROM threads WHERE id=$thread_id"))[0]==getAnonID())
        $op="true";
    else $op="false";


    $id = pg_fetch_row(pg_query("INSERT INTO posts (text, op, sage, anon_id, thread_id) VALUES ('$text', $op, $sage, $anon_id, $thread_id) RETURNING id"))[0];
    
    if(isset($_FILES['file']['name'])) {
        
        $total_files = count($_FILES['file']['name']);
        if($total_files > 4) $total_files = 4;
        
        for($i = 0; $i<$total_files; $i++) {
            
            $ext = strtolower(pathinfo($_FILES["file"]["name"][$i], PATHINFO_EXTENSION));
            
            if( !in_array( $ext, array('jpg', 'jpeg', 'png', 'gif', 'mp4','webm','ogg','mp3','rar','zip')))
                continue;
            
            if($_FILES['file']['size'][$i] > MAXFILESIZE)
                continue;
            $name = substr(hash("adler32",microtime().basename($_FILES['file']['name'][$i])),0,20).'.'.$ext;
            $uploadfile = UPLOADDIR.$thread_id.'/'.$name;
            $file = $thread_id.'/'.$name;
            move_uploaded_file($_FILES['file']['tmp_name'][$i], $uploadfile);
            
            //get preview
            if(in_array($ext, array('mp4','webm')))
                exec(FFMPEG." -i $uploadfile -deinterlace -an -ss 00:00:05 -f mjpeg -t 1 -r 1 -y -s 300x300 $uploadfile.jpg 2>&1");
           
            pg_query("INSERT INTO media (type,uri,post_id) VALUES ('$ext','$file',$id)");
        }
        
    }
    header("Location: #");  //For exclude "verify form send" by move backward in browser
}

//Create thread in board
function createThread($board_id) {
    getSafeID($board_id);
    $anon_id = getAnonID();
    $id = pg_fetch_row(pg_query("INSERT INTO threads (board_id, op_id) VALUES ($board_id, $anon_id) RETURNING id"))[0];
    mkdir(UPLOADDIR.$id, 0755);
    sendPost($id);
    header("Location: /".$_GET['board']."/".$id);
}

//Get anon id by hash from cookie.
function getAnonID() {
    //Hash is hash(IP+USERAGENT) what is good identifier of client
    if(isset($_COOKIE['session_id'])){
        $hash = getSafeText($_COOKIE['session_id']);
        $result = pg_query("SELECT * FROM anonym WHERE hash='$hash'");
        //If no records on db about this user then create one
        if(pg_num_rows($result) == 0) {
            $hash = hash("sha256",$_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"]);
            setcookie('session_id', $hash, [
                'path' => '/',
                'domain' => '13ch.tk',
                'expires' => time()+NEVEREXPIRING,
                'secure' => true,
                'httponly' => true,
                'samesite' => 'strict',
            ]);
            pg_query("INSERT INTO anonym (hash) VALUES ('$hash')");
        }
    } else {
        $hash = hash("sha256",$_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"]);
        setcookie('session_id', $hash, [
            'path' => '/',
            'domain' => '13ch.tk',
            'expires' => time()+NEVEREXPIRING,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'strict',
        ]);       
        $result = pg_query("SELECT * FROM anonym WHERE hash='$hash'");
        //If no records on db about this user then create one
        if(pg_num_rows($result) == 0) 
            pg_query("INSERT INTO anonym (hash) VALUES ('$hash')");
    }

    //After all extract id from db
    $result = pg_query("SELECT id FROM anonym WHERE hash='$hash'");
    return pg_fetch_row($result)[0];
}

//Create random string
function getRandomString($length = 16) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $charactersLength = strlen($characters);
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//Check text safe for sending post (exclude html tags and js scripts)
function getSafeText($text) {
    return pg_escape_string(htmlspecialchars(stripslashes(trim($text))));
}
function getSafeID($id) {
    if(is_numeric($id) && $id > 0)
        return $id;
    else
        return error();
}
//Three funcs lower this comment is returning count of records of thing in db
function getBoardsCount(){
    return pg_fetch_row(pg_query("SELECT count(*) FROM boards"))[0];
}
function getThreadsCount(){
    return pg_fetch_row(pg_query("SELECT count(*) FROM threads"))[0];
}
function getPostsCount(){
    return pg_fetch_row(pg_query("SELECT count(*) FROM posts"))[0];
}
function getAnonsCount(){
    return pg_fetch_row(pg_query("SELECT count(*) FROM anonym"))[0];
}
function whereAmI(){
    if(!isset($_GET["board"]) || strlen($_GET["board"])>8)
        return error();  //Redirect to home
    else if(!isset($_GET["thread"]))
        return "board";  //Inside board
    else
        return "thread"; //Inside thread
}
function getBoard($board_uri) {
    $board_uri=getSafeText($board_uri);
    return pg_query("SELECT * FROM boards WHERE uri='$board_uri'");
}
function getThreadPreview($thread_id){
    getSafeID($thread_id);
    $result = pg_query("(SELECT * FROM posts WHERE thread_id=$thread_id ORDER BY id LIMIT 1)
                        UNION
                        (SELECT * FROM posts WHERE thread_id=$thread_id ORDER BY id DESC LIMIT 3)
                        ORDER BY id");
    return $result;
}
function getPictures($post_id){
    getSafeID($post_id);

    return pg_query("SELECT * FROM media WHERE post_id=$post_id");
}
function error($error_number = 404) {
    if($error_number==404)
        header("Location: 404.html");
    return false;
}
function parseThreads(){
    $anon_id = getAnonID();

    $board = pg_fetch_array(getBoard($_GET["board"]));
    //Get array of all threads of, as example, /b/
    $query = getAllThreads($_GET["board"]);
    while($threads=pg_fetch_array($query)){
        //For every thread get 4 posts preview: one main as OP and three extra
        $main_post = true;
        $request = getThreadPreview($threads["thread_id"]);
        while($posts = pg_fetch_array($request)) {
            ?>
            <article class="<?php if($main_post) echo "main-post"; else echo "post" ?>">
                <a name="<?php echo $posts["id"] ?>"></a>
                <p class="header-post">
                    <?php
                    echo $board["anon_name"].' '.date('D j F Y H:i:s',strtotime($posts["date"])).' №'.$posts["id"].' ';
                    if($posts["op"] == "t")
                        echo " <span style='color:green'>OP</span> ";
                    if($posts["sage"] == "t")
                        echo " <span style='color:red'>SAGE!</span> ";
                    if($anon_id == $posts["anon_id"])
                        echo " (<span style='color:red'>Я</span>) ";
                    if($main_post) {
                        echo "<a href='/".$_GET["board"]."/".$threads["thread_id"]."'>Ответить</a>";
                    }
                    ?>
                </p>
                <div class="pictures">
                    <?php
                    $quereq = getPictures($posts["id"]);
                    while($pictures=pg_fetch_array($quereq)) {
                        ?>
                        <a class="picture" target="_blank" href="/img/upload/<?php echo $pictures["uri"] ?>">
                            <p><?php echo shortFileURI($pictures["uri"]) ?></p>
                            <img width="100" height="100" src="/img/upload/<?php 
                                                                            if(in_array($pictures['type'],array('rar','zip')))
                                                                                echo "../archive.png"; 
                                                                            else if(in_array($pictures['type'],array('mp3','ogg')))
                                                                                echo "../audio.png";   
                                                                            else if(in_array($pictures['type'],array('mp4','webm')))
                                                                                echo $pictures["uri"].".jpg";
                                                                            else
                                                                                echo $pictures["uri"]; 
                                                                        ?>"/>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <div class="text">
                    <p><?php 
                    $text = $posts["text"];
                    if($main_post){
                        if(strlen($text)>SHORTTEXT)
                            $text = substr($text,0,SHORTTEXT)."<a href='/".$_GET["board"]."/".$threads["thread_id"]."'> Показать полностью...</a>";
                        $main_post=false;
                    }
                    echo closeTags($text);
                    ?></p>
                </div>
            </article>
            <?php
        }
    }
}
function parsePosts() {
    $anon_id = getAnonID();
    $board = pg_fetch_array(getBoard($_GET["board"]));
    $query = getAllPosts($_GET["thread"]);
    $main_post = true;
    for($i=1; $posts=pg_fetch_array($query); $i++) {
        ?>
        <article class="<?php if($main_post) echo "main-post"; else echo "post"; $main_post=false; ?>">
            <a name="<?php echo $posts["id"] ?>"></a>
            <p class="header-post">
                <?php
                echo $board["anon_name"].' '.date('D j F Y H:i:s',strtotime($posts["date"])).' №'.$posts["id"].' #'.$i;

                if($posts["op"] == "t")
                    echo " <span style='color:green'>OP</span>";
                if($posts["sage"] == "t")
                    echo " <span style='color:red'>SAGE!</span>";
                if($anon_id == $posts["anon_id"])
                    echo " (<span style='color:red'>Я</span>)";

                ?>
            </p>
            <div class="pictures">
                <?php
                $request = getPictures($posts["id"]);
                while($pictures=pg_fetch_array($request)) {
                    ?>
                    <a class="picture" target="_blank" href="/img/upload/<?php echo $pictures["uri"] ?>">
                        <p><?php echo shortFileURI($pictures["uri"]) ?></p>
                        <img width="100" height="100" src="/img/upload/<?php 
                                                                            if(in_array($pictures['type'],array('rar','zip')))
                                                                                echo "../archive.png"; 
                                                                            else if(in_array($pictures['type'],array('mp3','ogg')))
                                                                                echo "../audio.png";   
                                                                            else if(in_array($pictures['type'],array('mp4','webm')))
                                                                                echo $pictures["uri"].".jpg"; 
                                                                            else
                                                                                echo $pictures["uri"]; 
                                                                        ?>"/>
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

function replaceTags($text) {
    $text=$text.' ';
    while(($pos = strpos($text,"&gt;&gt;")) !== false && ($text[$pos-1]==' ' || $text[$pos-1]=="\n" || $pos-1==0) && $text[$pos+8]!='&') { //link post
        $space = strpos($text,' ',$pos);
        $n = strpos($text,"\n",$pos);
        $post_id = substr($text,$pos+8,strlen($text)-($pos+8)-(strlen($text)-(($space<$n)?$space:$n)));
        $text = substr_replace($text,'<a href="#'.$post_id.'">>>',$pos,8);
        
        if($space<$n) {
            $text = substr_replace($text,'</a>',strpos($text,' ',$pos+8),0);
        }
        else {
            $text = substr_replace($text,'</a>',strpos($text,"\n",$pos+8),0);
        }
    }
    
    while(($pos = strpos($text,"&gt;")) !== false && $text[$pos-1]!=';' && $text[$pos+4]!='&') { //greentext
        $text = substr_replace($text,'<span style="color:green">>',$pos,4);
        $text = substr_replace($text,'</span><br>',strpos($text,"\n"),1); 
    }
    //$text = str_replace("[green]",'<span style="color:green">',$text);
    //$text = str_replace("[/green]","</span><br>",$text);
    
    $text = str_replace("\n","<br>",$text); //Multiline text
    $text = str_replace("[b]","<b>",$text); //Bold text
    $text = str_replace("[/b]","</b>",$text);
    $text = str_replace("[i]","<i>",$text);
    $text = str_replace("[/i]","</i>",$text);
    $text = str_replace("[s]","<s>",$text); //Strike text
    $text = str_replace("[/s]","</s>",$text);
    $text = str_replace("[u]","<u>",$text); //Underline text
    $text = str_replace("[/u]","</u>",$text);
    $text = str_replace("[o]",'<span class="overline" >',$text);    //Overline text
    $text = str_replace("[/o]","</span>",$text);
    $text = str_replace("[sup]","<sup>",$text);
    $text = str_replace("[/sup]","</sup>",$text);
    $text = str_replace("[sub]","<sub>",$text);
    $text = str_replace("[/sub]","</sub>",$text);
    $text = str_replace("[spoiler]",'<span class="spoiler">',$text);    //Text under spoiler
    $text = str_replace("[/spoiler]","</span>",$text);
    
    return closeTags($text);
}

//Imported from stackoverflow.com/questions/3810230/close-open-html-tags-in-a-string
function closeTags($html) {
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}
function shortFileURI($uri) {
    $uri = substr(stristr($uri,'/'),1); //Delete '/' symbol that links to folder on server. Saves only name
    if(strlen($uri)>=13)    //If file name >=13 symbols
        $uri = substr($uri,0,6).'[...]'.substr($uri,strripos($uri,'.')); //Shorter URI. First 5 symbols + extension of file.
    return $uri;
}

/*function parseThreads(){
    $anon_id = getAnonID();

    $board = pg_fetch_array(getBoard($_GET["board"]));
    //Get array of all threads of, as example, /b/
    $query = getAllThreads($_GET["board"]);
    while($threads=pg_fetch_array($query)){
        //For every thread get 4 posts preview: one main as OP and three extra
        $main_post = true;
        $request = getThreadPreview($threads["id"]);
        while($posts = pg_fetch_array($request)) {
            ?>
            <article class="<?php if($main_post) echo "main-post"; else echo "post" ?>">
                <a name="<?php echo $posts["id"] ?>"></a>
                <p class="header-post">
                    <?php
                    echo $board["anon_name"].' '.date('D j F Y H:i:s',strtotime($posts["date"])).' №'.$posts["id"].' ';
                    if($posts["op"] == "t")
                        echo " <span style='color:green'>OP</span> ";
                    if($posts["sage"] == "t")
                        echo " <span style='color:red'>SAGE!</span> ";
                    if($anon_id == $posts["anon_id"])
                        echo " (<span style='color:red'>Я</span>) ";
                    if($main_post) {
                        echo "<a href='/".$_GET["board"]."/".$threads["id"]."'>Ответить</a>";
                    }
                    ?>
                </p>
                <div class="pictures">
                    <?php
                    $quereq = getPictures($posts["id"]);
                    while($pictures=pg_fetch_array($quereq)) {
                        ?>
                        <a class="picture" href="/img/upload/<?php echo $pictures["uri"] ?>">
                            <p><?php echo shortFileURI($pictures["uri"]) ?></p>
                            <img src="/img/upload/<?php echo $pictures["uri"] ?>" width="100" height="100" >
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <div class="text">
                    <p><?php 
                    $text = $posts["text"];
                    if($main_post){
                        if(strlen($text)>SHORTTEXT)
                            $text = substr($text,0,SHORTTEXT)."<a href='/".$_GET["board"]."/".$threads["id"]."'> Показать полностью...</a>";
                        $main_post=false;
                    }
                    echo closeTags($text);
                    ?></p>
                </div>
            </article>
            <?php
        }
    }
}*/

/*function getAllThreads($board_uri) {
    //Get safe input of board uri
    $board_uri = getSafeText($board_uri);
    //Ask for id of board
    $query = pg_query("SELECT id FROM boards WHERE uri='$board_uri'");
    //If no such board then return false
    if(pg_num_rows($query)==0) return error();
    //Else get board id and ask for all threads for this board
    $board_id = pg_fetch_row($query)[0];
    return pg_query("SELECT * FROM threads WHERE board_id=$board_id");
}*/
