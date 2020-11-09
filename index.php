<?php

$uri = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $uri);                // Split path on slashes

if($uri === '/')
    require 'home.php';
elseif(count($elements)===1 or empty($elements[1])) {
    $_GET['board']=$elements[0];
    require("null.php");
}
elseif(count($elements)===2) {
    $_GET['board']=$elements[0];
    $_GET['thread']=$elements[1];
    require("null.php");
}
else
    require '404.html';

?>