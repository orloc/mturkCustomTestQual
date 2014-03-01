<?php 

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

$conf = require __DIR__.'/config/parameters.php';

$client = new Client();
$method = 'post';
$url = $conf['url'];
unset($conf['url']);

$request = $client->$method($url);
$query = $request->getQuery();

$additonalParams = [
    'Signature' => generateSig($conf, $url, $method),
    'Timestamp' => getDateTime(),
];

ksort(array_merge($conf, $additonalParams));

foreach ($conf as $k => $v) {
    $query->add($k, $v);
}

$response = $request->send();

var_dump($response->getBody(true));

function generateSig(array $conf, $url, $method) { 
    $hmacString = $conf['Service'].$conf['Operation'].getDateTime();
    $canonicalQ = implode('&', $conf);
    $key = "$method\n$url\n$canonicalQ}";

    $hmac = hash_hmac('sha256', $hmacString, $key, true);

    $sig = urlencode(base64_encode($hmac));

    return $sig;
}

function getDateTime(){
    $date = new \DateTime();
    return $date->format('Y-m-d');
}





