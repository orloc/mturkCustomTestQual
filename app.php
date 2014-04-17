<?php
require_once __DIR__.'/vendor/autoload.php';

use MechWrapper\Command;
use MechWrapper\Response\TurkResponse;

const debug = false;

$conf = require __DIR__.'/config/parameters.php';

$qType = new Command\QualificationType($conf['QualificationType'], debug);

$qType->addQualificationTest(__DIR__.'/resources/qualifcations/questionairre.xml');
$qType->addAnswerKey(__DIR__.'/resources/qualifcations/answers.xml');


$qResponse = $qType->trySendRequest();

$qualResponse = new TurkResponse($qResponse, 'QualificationType');

if ($qualResponse->isValid()) { 
    $qualTypeId = $qualResponse->getXmlPath('QualificationType/QualificationTypeId');
    $reward = $conf['HIT']['Reward'];
    unset($conf['HIT']['Reward']);

    $hit = new Command\Hit($conf['HIT'], debug);
    $hit->addQualificationRequirement($qualTypeId,'EqualTo', 3)
        ->addReward($reward)
        ->addQuestion(__DIR__.'/resources/hits/question.xml');
    

    $hResponse = $hit->trySendRequest(); 

    $hitResponse = new TurkResponse($hResponse, 'HIT');

    if (!$hitResponse->isValid()) { 

        var_dump($hitResponse->getBody());die('hit, died');

    }

    var_dump($hitResponse->getBody());

} else { 
    var_dump($qualResponse->getBody());die('qual, died');
}
