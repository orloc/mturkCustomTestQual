<?php

namespace MechWrapper\Command;

use MechWrapper\Request\AbstractAWSRequest;

class QualificationType extends AbstractAWSRequest { 
    protected static $parameters = [ 
        'Name',
        'Description',
        'Keywords',
        'QualificationTypeStatus',
        'AutoGranted'
    ]; 
    

    public function __construct(array $config, $debug = false) { 
        parent::__construct($config, self::$parameters, $debug);
    }

    public function addQualificationTest($testXml){ 
        return $this->addXmlParam($testXml, 'Test');
    }

    public function addAnswerKey($answXml) { 
        return $this->addXmlParam($answXml, 'AnswerKey');
    }
    

}
