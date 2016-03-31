<?php

// Test TLS certificate verification in PHP
// (c) Sucuri, 2016

// Try to download using cURL, return true in case of successful completion
function tryCurl($url)
{
    $ch = curl_init($url);
    
    if (!$ch) {
        exit('curl_init failed');
    }
    
    if (!curl_setopt($ch, CURLOPT_RETURNTRANSFER, true)) {
        exit('curl_setopt failed');
    }
    
    // Set "verify peer" and "verify host" options for older PHP versions
    /*if (!curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true) ||
        !curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2)) {
        exit('curl_setopt failed');
    }*/
    
    $res = curl_exec($ch);
    
    curl_close($ch);
    return $res;
}


// Try to connect using stream functions and download the first 8 KB
// (or the whole response in some cases); return true in case of success

function tryFopen($url)
{
    $stream = @fopen($url, 'r');
    if ($stream === false) {
        return false;
    }
    
    $res = stream_get_contents($stream) !== false;
    
    fclose($stream);
    return $res;
}

function tryFsockopen($url)
{
    $host = parse_url($url, PHP_URL_HOST);
    $stream = @fsockopen('tls://' . $host, 443);
    if ($stream === false) {
        return false;
    }
    
    if (fwrite($stream, "GET / HTTP/1.1\r\nHost: " . $host . "\r\n\r\n") === false ||
        fread($stream, 8192) === false) {
        exit('read/write failed');
    }
    
    fclose($stream);
    return true;
}

function tryTlsTransport($url)
{
    $host = parse_url($url, PHP_URL_HOST);
    $stream = @stream_socket_client('tls://' . $host . ':443');
    if ($stream === false) {
        return false;
    }
    
    if (fwrite($stream, "GET / HTTP/1.1\r\nHost: " . $host . "\r\n\r\n") === false ||
        fread($stream, 8192) === false) {
        exit('read/write failed');
    }

    fclose($stream);
    return true;
}


function trySocketEnableCrypto($url)
{
    $host = parse_url($url, PHP_URL_HOST);
    $stream = stream_socket_client('tcp://' . $host . ':443');
    if (!$stream) {
        exit('stream_socket_client failed');
    }
    
    $res = @stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    if ($res && (fwrite($stream, "GET / HTTP/1.1\r\nHost: " . $host . "\r\n\r\n") === false ||
                 fread($stream, 8192) === false)) {
        exit('read/write failed');
    }

    fclose($stream);
    return $res;
}

function tryFileGetContents($url)
{
    return @file_get_contents($url) !== false;
}



$methods = array('tryCurl', 'tryFopen', 'tryFsockopen',
    'tryTlsTransport', 'trySocketEnableCrypto', 'tryFileGetContents');

$urls = array(
    'revoked'     => 'https://revoked.grc.com/',
    'expired'     => 'https://qvica1g3-e.quovadisglobal.com/',
    'expired2'    => 'https://expired.badssl.com/',
    'self-signed' => 'https://self-signed.badssl.com/',
    'bad domain'  => 'https://wrong.host.badssl.com/',
    'bad domain2' => 'https://tv.eurosport.com/',
    'rc4'         => 'https://rc4.badssl.com/',
    'dh480'       => 'https://dh480.badssl.com/',
    'superfish'   => 'https://superfish.badssl.com/',
    'edellroot'   => 'https://edellroot.badssl.com/',
    'dsdtest'     => 'https://dsdtestprovider.badssl.com/'
);

// Try all URLs for all methods. TLS certificate verification must fail for each URL;
// print "INCORRECT" if PHP allows an invalid certificate
foreach ($methods as $method) {
    echo '=== ' . $method . " ===\n";
    foreach ($urls as $name => $url) {
        $res = call_user_func($method, $url);
        echo $name . ' - ' . ($res ? "INCORRECT\n" : "correct\n");
    }
}
