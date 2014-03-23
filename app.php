<?php
require_once __DIR__.'/vendor/autoload.php';

use MechWrapper\Command;
use MechWrapper\Response\TurkResponse;

$conf = require __DIR__.'/config/parameters.php';

$qType = new Command\QualificationType($conf['QualificationType']);

$qType->addQualificationTest(__DIR__.'/resources/qualifcations/questionairre.xml');
$qType->addAnswerKey(__DIR__.'/resources/qualifcations/answers.xml');


$response = $qType->trySendRequest();

$qualResponse = new TurkResponse($response, 'QualificationType');
if ($qualResponse->isValid()) { 
    $qualTypeId = $qualResponse->getXmlPath('QualificationType/QualificationTypeId');

    $reward = $conf['Hit']['Reward'];
    unset($conf['Hit']['Reward']);

    $hit = new Command\Hit($conf['Hit']);
    $hit->addQualificationRequirement($qualTypeId)
        ->addReward($reward);

    
} else { 
    var_dump($qualResponse->getErrors());
}

