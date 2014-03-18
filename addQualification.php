<?php 

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

$conf = require __DIR__.'/config/parameters.php';

// prep our http client and grab some things

$client = new Client();
$dec = new Colors();

$url = $conf['url'];
$key = $conf['key'];

$awsAKId = $conf['AWSAccessKeyId'];

foreach ($conf as $k => $config) { 
    if (is_array($config)){ 
        $conf[$k]['AWSAccessKeyId']= $awsAKId;
    }
}

/*
 * Creates a Qualification Type 
 * Defined in the corresponding XML Documents
 */
$qual = __DIR__.'/resources/qualifcations/questionairre.xml';
$answ = __DIR__.'/resources/qualifcations/answers.xml';

// grab the test qualifcation 
$qualXml = simplexml_load_file($qual);
$answXml = simplexml_load_file($answ);

$creds = generateSig($conf['QualificationType'], $key);

$additonalParams = [
    'Signature' => $creds[0],
    'Timestamp' => $creds[1],
    'Test' => trim($qualXml->asXml(), " \t\n\r"),
    'AnswerKey' => (string)$answXml->asXml(),
    'TestDurationInSeconds' => '1000'
];

$params = array_merge($conf['QualificationType'], $additonalParams);
ksort($params);

$request = $client->post($url, [], $params,[ 'debug' => true ]);

$response = tryAWSRequest($request);

//===========================

if (!checkValidRequest($response, 'QualificationType')){
    $code = parseXMLResponse($response, 'QualificationType/Request/Errors/Error/Code');
    $message = parseXMLResponse($response, 'QualificationType/Request/Errors/Error/Message');

    echo $dec->getColoredString("Error!\n", 'red');
    echo $dec->getColoredString(sprintf("Code: %s\nMessage: %s\n\n", $code, $message), 'cyan');

} else { 
    echo $dec->getColoredString(sprintf("\nSuccess! \nStatus Code: %s \n\n", $response->getStatusCode()), 'green');

    $hitQuestion = __DIR__.'/resources/hits/question.xml';
        
}

// =====================================================

function getQualifcationTypeId(Response $response){ 
    $data = parseXMLResponse($response, 'QualificationType/QualificationTypeId');

    return count($data) == 30 ? $data : false;
}

function checkValidRequest(Response $response, $requestType) { 
    $types = [ 
        'QualificationType'
    ];

    if (!in_array($requestType, $types)) { 
        throw new \Exception('Request type not supported');
    }

    $data = parseXMLResponse($response, "$requestType/Request/IsValid");

    if (!is_array($data) && strtolower($data) != 'false'){
        return true;
    }

    return false;
}

function responseToXml(Response $response) { 
    return $response->getBody(true);
 }

function parseXMLResponse($data, $path){ 
    $data = responseToXml($data);

    $xml = simplexml_load_string($data);

    $result = $xml->xpath($path);

    $ret = [];
    while (list(,$node) = each($result)) {
        $ret[] = (string)$node;
    } 

    return count($ret) > 1 ? $ret : $ret[0];
}

function tryAWSRequest(Request $request) { 

    $dec = new Colors();
    
    try { 
        $response = $request->send();

        return $response;
    } catch (\Guzzle\Http\Exception\CurlException  $e) {
        echo $dec->getColoredString("\n\nError:\n", 'red');
        var_dump($e);
    } catch (\Exception $e) {
        echo $dec->getColoredString("\n\nGeneral Error:\n", 'red');
        var_dump($e->getMessage());
    }
    die;
}

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
