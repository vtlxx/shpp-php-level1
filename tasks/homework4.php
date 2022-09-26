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
            $headers[$splited_header[0]] = $splited_header[1];
            //$headers[$i-1] = array($splited_header[0], $splited_header[1]);
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
    $filename = 'assets/passwords.txt';
    //check uri and content-type
    if(!str_starts_with($uri, '/api/checkLoginAndPassword') || !$headers["Content-Type"] == "application/x-www-form-urlencoded"){
        outputHttpResponse(400, 'Bad Request',
            array('Date' => date('r'), 'Server' => 'Apache/2.2.14 (Win32)', 'Content-Length' => 9,
                'Connection' => 'Closed', 'Content-Type' => 'text/html; charset=utf-8'), 'not found');
        return;
    }

    //getting value of login and password from body
    $user_data = explode('&', $body);

    //getting login and password
    $login = explode('=', $user_data[0])[1];
    $password = explode('=', $user_data[1])[1];

    //getting and checking login and password from file
    if(!file_exists($filename)){
        outputHttpResponse(500, 'INTERNAL ERROR',
            array('Date' => date('r'), 'Server' => 'Apache/2.2.14 (Win32)', 'Content-Length' => 0,
                'Connection' => 'Closed', 'Content-Type' => 'text/html; charset=utf-8'), '');
        return;
    }
    $data = explode("\n", file_get_contents($filename));
    for($i = 0; $i < sizeof($data); ++$i){
        if(explode(':', $data[$i])[0] == $login && explode(':', $data[$i])[1] == $password){
            $body_output = '<h1 style="color:green">FOUND</h1>';
            outputHttpResponse(200, 'OK',
                array('Date' => date('r'), 'Server' => 'Apache/2.2.14 (Win32)', 'Content-Length' => strlen($body_output),
                    'Connection' => 'Closed', 'Content-Type' => 'text/html; charset=utf-8'), $body_output);
            return;
        }
    }

    //if password doesn't exist
    $body_output = '<h1 style="color:red">INCORRECT LOGIN OR PASSWORD</h1>';
    outputHttpResponse(200, 'OK',
        array('Date' => date('r'), 'Server' => 'Apache/2.2.14 (Win32)', 'Content-Length' => strlen($body_output),
            'Connection' => 'Closed', 'Content-Type' => 'text/html; charset=utf-8'), $body_output);
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

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);