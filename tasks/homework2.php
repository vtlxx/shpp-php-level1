<?php

// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
function readHttpLikeInput() {
    $f = fopen( 'php://stdin', 'r' );
    $store = "";
    $toread = 0;
    while( $line = fgets( $f ) ) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/',$line,$m))
            $toread=$m[1]*1;
        if ($line == "\r\n")
            break;
    }
    if ($toread > 0)
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function parseTcpStringAsHttpRequest($string) {
    //splitting by lines
    $split_string = explode("\n", $string);
    //splitting first line by spaces
    $first_line = explode(' ', $split_string[0]);

    //initializing variables
    $method = $first_line[0];
    $uri = $first_line[1];
    $headers = array();
    $body = "";

    //filling headers
    $i = 1;
    while($i < sizeof($split_string) && $split_string[$i] != ""){
        if(str_contains($split_string[$i], ': ')){
            $splited_header = explode(': ', $split_string[$i]);
            //$headers[$splited_header[0]] = $splited_header[1];
            $headers[$i-1] = array($splited_header[0], $splited_header[1]);
        }
        ++$i;
    }

    //filling body
    if(!str_contains($split_string[sizeof($split_string)-1], ': ')){
        $body = $split_string[sizeof($split_string)-1];
    }

    return array(
        "method" => $method,
        "uri" => $uri,
        "headers" => $headers,
        "body" => $body,
    );
}

$http = parseTcpStringAsHttpRequest($contents);
echo(json_encode($http, JSON_PRETTY_PRINT));
