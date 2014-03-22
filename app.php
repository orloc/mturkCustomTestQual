<?php
require_once __DIR__.'/vendor/autoload.php';

use MechWrapper\Command;

$conf = require __DIR__.'/config/parameters.php';

$qType = new Command\QualificationType($conf['QualificationType']);

$qType->addQualificationTest(__DIR__.'/resources/qualifcations/questionairre.xml');
$qType->addAnswerKey(__DIR__.'/resources/qualifcations/answers.xml');


$request = $qType->prepareRequest();
$response = $qType->trySendRequest($request);

var_dump($response->getBody(true));

