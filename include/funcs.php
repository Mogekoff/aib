<?php

//Never expiring date for cookies
const NEVEREXPIRING = 10 * 365 * 24 * 60 * 60;  //10 years

//Database Connection
$dbconn = pg_connect("host=localhost dbname=chan user=postgres password=ldw;qodk2");

//Identify anon and set cookie
getAnonID();

//Get all posts by thread
function getAllPosts($thread_id) {
    getSafeID($thread_id);

    return pg_query("SELECT * FROM posts WHERE thread_id=$thread_id");
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
    return pg_query("SELECT * FROM threads WHERE board_id=$board_id");
}

//Get all boards
function getAllBoards() {
    return pg_query("SELECT * FROM boards ORDER BY id");
}

//Send post to thread
function sendPost($thread_id) {
    getSafeID($thread_id);
    $text = getSafeText($_POST["text"]);
    if(strlen($text)==0) return error();
    $anon_id = getAnonID();
    $op = $_POST["op"];
    $sage = $_POST["sage"];
    pg_query("INSERT INTO posts (id, text, op, sage, anon_id) VALUES ($thread_id, '$text', $op, $sage, $anon_id)");
}

//Create thread in board
function createThread($board_id) {
    getSafeID($board_id);

    $anon_id = getAnonID();
    pg_query("INSERT INTO threads (board_id, op_id) VALUES ($board_id, $anon_id)");
}

//Get anon id by hash from cookie.
function getAnonID() {
    //Hash is hash(IP+USERAGENT) what is good identifier of client
    $hash = password_hash($_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"], PASSWORD_DEFAULT);
    setcookie("id", $hash, NEVEREXPIRING, "/", null, true, null);
    $result = pg_query("SELECT * FROM anonym WHERE hash=$hash");
    //If no records on db about this user then create one
    if(pg_num_rows($result) == 0)
        pg_query("INSERT INTO anonym VALUES $hash");

    //After all extract id from db
    $result = pg_query("SELECT id FROM anonym WHERE hash=$hash");
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
    if(is_numeric($id))
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
    $result = pg_query("(SELECT * FROM posts WHERE thread_id=$thread_id ORDER BY id DESC LIMIT 3)
                    UNION
                    (SELECT * FROM posts WHERE thread_id=$thread_id ORDER BY id LIMIT 1)");

    return $result;
}
function getPictures($post_id){
    getSafeID($post_id);

    return pg_query("SELECT * FROM media WHERE post_id=$post_id");
}
function error($error_number = 404) {
    if($error_number==404)
        header("Location: /404.html");
    return false;
}