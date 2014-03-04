<?php 

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

$conf = require __DIR__.'/config/parameters.php';

$client = new Client();
$url = $conf['url'];
$key = $conf['key'];

unset($conf['key']);
unset($conf['url']);

$request = $client->get($url, [], [ 'debug' => true ]);
$query = $request->getQuery();

$creds = generateSig($conf, $key);

$additonalParams = [
    'Signature' => $creds[0],
    'Timestamp' => $creds[1],
];

$params = array_merge($conf, $additonalParams);
ksort($params);


foreach ($params as $k => $v) {
    $query->add($k, $v);
}

try { 
$response = $request->send();
var_dump($response->getBody(true));
} catch (\Exception $e) {
    var_dump($e->getResponse()->getBody(true));
}
/**
 *  
 */
function generateSig(array $conf, $key) { 

    $ts = getDateTime();
    $hmacString = $conf['Service'].$conf['Operation'].$ts;

    $hmac = hash_hmac('sha1', $hmacString, $key, true);

    $sig = base64_encode($hmac);


    return [$sig, $ts];
}

function getDateTime($format = 'Y-m-d\TH:i:s\Z'){
    $date = new \DateTime('now', new DateTimeZone('UTC'));
    return $date->format($format);
}
