<?php

namespace MechWrapper\Command;

use MechWrapper\Request\AbstractAWSRequest;

class QualificationType extends AbstractAWSRequest { 
    
    public function __construct(array $config) { 
        parent::__construct($config);
    }
}
