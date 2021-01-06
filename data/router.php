<?php
require_once("php/engine.php");
$uri = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $uri);                // Split path on slashes
$_GET['board']=$elements[0];
$_GET['thread']=$elements[1];
$board = pg_fetch_array(getBoard($_GET["board"]));

if(empty($uri))
    require('index.php');
elseif(count($elements)===1 or empty($elements[1])) { 
    if(empty($board))
        require('404.html');
    else      
        require("null.php");   
}
elseif(count($elements)===2) {    
    $thread = pg_fetch_array(getThreadPreview($_GET['thread']));
    if(empty($thread))
        require('404.html');
    else      
        require("null.php"); 
}
else
    require('404.html');

?>