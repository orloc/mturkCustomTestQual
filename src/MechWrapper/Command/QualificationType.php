<?php

namespace MechWrapper\Command;

use MechWrapper\Request\AbstractAWSRequest;

class QualificationType extends AbstractAWSRequest { 
    protected static $parameters = [ 
    ]; 

    public function __construct(array $config, $debug = false) { 
        parent::__construct($config);
    }

    public function checkRequestParams(array $config){ 
    }


}
