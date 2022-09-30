<?php

/*function readHttpLikeInput()
{
    $f = fopen('php://stdin', 'r');
    $store = "";
    $toread = 0;
    while ($line = fgets($f)) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/', $line, $m))
            $toread = $m[1] * 1;
        if ($line == "\r\n")
            break;
    }
    if ($toread > 0)
        $store .= fread($f, $toread);
    return $store;
}*/

//$contents = readHttpLikeInput();

function parseTcpStringAsHttpRequest($string)
{
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
    while ($i < sizeof($split_string) && $split_string[$i] != "") {
        if (str_contains($split_string[$i], ': ')) {
            $splited_header = explode(': ', $split_string[$i], 2);
            $headers[$splited_header[0]] = $splited_header[1];
            //$headers[$i-1] = array($splited_header[0], $splited_header[1]);
        }
        ++$i;
    }
    print_r($headers);
    //echo "\n{" . strlen($headers["Host"]) . "}\n\n";
    //filling body
    if (!str_contains($split_string[sizeof($split_string) - 1], ': ')) {
        $body = $split_string[sizeof($split_string) - 1];
    }

    return array(
        "method" => $method,
        "uri" => $uri,
        "headers" => $headers,
        "body" => $body,
    );
}

function processHttpRequest($method, $uri, $headers, $body)
{
    $filepath = '';

    if($headers["Host"] == 'student.shpp.me' || $headers["Host"] == 'another.shpp.me'){
        $filepath .= explode('.', $headers["Host"])[0];
    } else{
        $filepath .= 'else';
    }
    $filepath .= $uri == '/' ? '/index.html' : $uri;
    if(file_exists($filepath)){
        $value = file_get_contents($filepath);
        outputHttpResponse(200, "OK", array('Date' => date('r'),
            'Server' => 'Apache/2.2.14 (Win32)', 'Content-Length' => strlen($value),
            'Connection' => 'Closed', 'Content-Type' => 'text/html; charset=utf-8'), $value);
    } else {
        outputHttpResponse(404, "Not found", array('Date' => date('r'), 'Server' => 'Apache/2.2.14 (Win32)', 'Content-Length' => 0,
            'Connection' => 'Closed', 'Content-Type' => 'text/html; charset=utf-8'), '');
    }
}

function outputHttpResponse($statuscode, $statusmessage, $headers, $body)
{
    /*$result = "HTTP/1.1 $statuscode $statusmessage
Date: " . date("r") . "
Server: Apache/2.2.14 (Win32)
Connection: Closed
Content-Type: text/html; charset=utf-8
Content-Length: " . strlen($body) . "

$body";*/
    $result = "HTTP/1.1 $statuscode $statusmessage\n";
    foreach($headers as $key => $value){
        $result .= "$key: $value\n";
    }
    $result .= "\n$body";
    echo $result;
}

/*$http = parseTcpStringAsHttpRequest("GET / HTTP/1.1
Host: student.shpp.me
Accept: image/gif, image/jpeg, *//*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0
");*/
$http = parseTcpStringAsHttpRequest("GET /test.txt HTTP/1.1\nHost: stude.shpp.me\nAccept: image/gif, image/jpeg, */*\nAccept-Language: en-us\nAccept-Encoding: gzip, deflate\nUser-Agent: Mozilla:4.0");

//$http = parseTcpStringAsHttpRequest($contents);

processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);