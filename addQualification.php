<?php 

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

$conf = require __DIR__.'/config/parameters.php';

$qual = __DIR__.'/resources/qualifcations/questionairre.xml';
$answ = __DIR__.'/resources/qualifcations/answers.xml';

// grab the test qualifcation 
$qualXml = simplexml_load_file($qual);
$answXml = simplexml_load_file($answ);


// prep our http client and grab some things
$client = new Client();
$url = $conf['url'];
$key = $conf['key'];

unset($conf['key']);
unset($conf['url']);


// -- Generate our request
$request = $client->get($url, [], [ 'debug' => true ]);
$query = $request->getQuery();

$creds = generateSig($conf, $key);

$additonalParams = [
    'Signature' => $creds[0],
    'Timestamp' => $creds[1],
    'Test' => trim($qualXml->asXml(), " \t\n\r"),
    'AnswerKey' => (string)$answXml->asXml()
];

var_dump(strlen($additonalParams['Test']), strlen($additonalParams['AnswerKey']));die;
$params = array_merge($conf, $additonalParams);
ksort($params);

foreach ($params as $k => $v) {
    $query->add($k, $v);
}

 
// Hope it works?
try { 
    $response = $request->send();
    print "HERE";
    print "success";
} catch (\Guzzle\Http\Exception\CurlException  $e) {
    var_dump($e);
} catch (\Exception $e) {
    print "general exception";
    var_dump($e->getMessage());
}


// =====================================================
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
