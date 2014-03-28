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

    public function addReward($amount) { 
        $this->addConfigParam('Reward.1.Amount', $amount)
            ->addConfigParam('Reward.1.CurrencyCode', 'USD');

        return $this;

    }

    public function addQualificationRequirement($qualId) { 
        $this->addConfigParam('QualificationRequirement.1.QualificationTypeId', $qualId)
            ->addConfigParam('QualificationRequirement.1.Comparator', 'Exists')
            ->addConfigParam('QualificationRequirement.1.RequiredToPreview', true);

        return $this;
    }
}
