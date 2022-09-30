<?php
$filename = "count.txt";
if(file_exists($filename)){
    $value = file_get_contents($filename);
    echo $value;
    file_put_contents("count.txt", $value+1);
} else{
    echo 1;
    file_put_contents("count.txt", 1);
}