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

    public function addQualificationRequirement($qualId, $comp = 'Exists', $compVal = null) { 

        if ($comp != 'Exists' && $compVal === null){ 
            throw new \Exception('Must specify integer value with comparitor that is non Exists');
        }

        $this->addConfigParam('QualificationRequirement.1.QualificationTypeId', $qualId)
            ->addConfigParam('QualificationRequirement.1.RequiredToPreview', true)
            ->addConfigParam('QualificationRequirement.1.Comparator', $comp);

        if ($comVal != null){ 
            $this->addConfigParam('QualificationRequirement.1.IntegerValue', 3);
        }

        return $this;
    }
}
