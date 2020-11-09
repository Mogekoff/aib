<?php
require_once("include/funcs.php");
$post_id = $_POST["post_id"];
$post_id = getSafeID($post_id);

$post = pg_fetch_array(pg_query("SELECT * FROM posts WHERE id=$post_id"));
//for ajax