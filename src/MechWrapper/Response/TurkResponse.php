<?php

namespace MechWrapper\Response;

use Guzzle\Http\Message\Response;

class TurkResponse { 

    protected $response;

    protected $request_type;

    private static $types = [ 
        'QualificationType',
        'HIT'
    ];

    public function __construct(Response $response, $requestType) { 
        self::validateType($requestType);

        $this->response = $response;
        $this->request_type = $requestType;
    }

    public function isValid() { 
        $data = $this->getXmlPath("{$this->getRequestType()}/Request/IsValid");

        if (!is_array($data) && strtolower($data) != 'false'){
            return true;
        }

        return false;
    }

    public function getXmlPath($path){ 
        $data = $this->responseToXml();
        $xml = simplexml_load_string($data);

        $result = $xml->xpath($path);

        $ret = [];
        while (list(,$node) = each($result)) {
            $ret[] = (string)$node;
        } 

        return count($ret) > 1 ? $ret : $ret[0];
    }

    public function getRequestType(){ 
        return $this->request_type;
    }

    public function getErrors() { 
        return [
            'Errors' => [
                'code' => $this->getXmlPath("{$this->getRequestType()}/Request/Errors/Error/Code"), 
                'error' => $this->getXmlPath("{$this->getRequestType()}/Request/Errors/Error/Message")
            ]
        ];
    }

    private function responseToXml() { 
        return $this->response->getBody(true);
    }

    private static function validateType($type){ 
        if (!in_array($type, self::$types)) { 
            throw new \Exception('Request type not supported');
        }
    }
}
