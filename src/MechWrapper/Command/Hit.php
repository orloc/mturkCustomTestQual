<?php

namespace MechWrapper\Command;

use MechWrapper\Request\AbstractAWSRequest;

class Hit extends AbstractAWSRequest { 

    protected static $parameters = [ 
        'Title',
        'Description',
        'AssignmentDurationInSeconds',
        'LifetimeInSeconds',
        'Keywords',
        'MaxAssignments',
        'AutoApprovalDelayInSeconds'
    ];
    
    public function __construct(array $config, $debug = false) { 
        parent::__construct($config, self::$parameters, $debug);
    }

    public function addQuestion($xml) { 
        return $this->addXmlParam($xml, 'Question');
    }

    public function addReward($xml) { 
        return $this->addXmlParam($xml, 'Reward');
    }

    public function addQualificationRequirement($qualId) { 
        $this->addConfigParam('QualificationTypeId', $qualID)
            ->addConfigParam('Comparator', 'Exists')
            ->addConfigParam('RequiredToPreview', true);
    }
}
