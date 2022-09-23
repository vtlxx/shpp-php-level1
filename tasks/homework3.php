<?php

function readHttpLikeInput()
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
}

$contents = readHttpLikeInput();

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
            $splited_header = explode(': ', $split_string[$i]);
            //$headers[$splited_header[0]] = $splited_header[1];
            $headers[$i-1] = array($splited_header[0], $splited_header[1]);
        }
        ++$i;
    }

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
    if ($method == "GET" && str_contains($uri, "?nums=") && str_starts_with($uri, "/sum")) {
        //transforming string with numbers to the array
        $index = strpos($uri, "?nums=");
        $nums_line = substr($uri, $index + 6);
        $nums = explode(',', $nums_line);

        //calculating sum of numbers
        $sum = 0;
        for ($i = 0; $i < sizeof($nums); ++$i) {
            $sum += (int)$nums[$i];
        }
        outputHttpResponse(200, 'OK', $headers, $sum);
    } else {
        outputHttpResponse(400, 'Bad Request', $headers, "not found");
    }
}

function outputHttpResponse($statuscode, $statusmessage, $headers, $body)
{
    $result = "HTTP/1.1 $statuscode $statusmessage
Date: " . date("r") . "
Server: Apache/2.2.14 (Win32)
Connection: Closed
Content-Type: text/html; charset=utf-8
Content-Length: " . strlen($body) . "

$body";
    echo $result;
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);