<?php

namespace MechWrapper\Response;

use Guzzle\Http\Message\Response;

class ResponseWrapper { 

    protected $response;

    public function __construct(Response $response) { 
        $this->response = $response;
    }

    private function responseToXml() { 
        return $this->response->getBody(true);
     }

    function parseXMLResponse($path){ 
        $data = $this->responseToXml();
        $xml = simplexml_load_string($data);

        $result = $xml->xpath($path);

        $ret = [];
        while (list(,$node) = each($result)) {
            $ret[] = (string)$node;
        } 

        return count($ret) > 1 ? $ret : $ret[0];
    }
}
