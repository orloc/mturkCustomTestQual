<?php

namespace MechWrapper\Request;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Exception\CurlException;

abstract class AbstractAWSRequest extends Request { 

    const AWSMechTurkURL = 'https://mechanicalturk.amazonnaws.com';

    protected $config;

    protected $debug;

    protected static $required_keys = [ 
        'AWSAccessKeyId',
        'AWSKey', 
        'Service', 
        'Operation'
    ];

    public function __construct(array $config = array(), $debug = false) { 
        if (!self::checkRequiredKeys($config)) { 
            throw new \Exception(sprintf("Config must contain: %s", implode(self::$required_keys, ', ')));
        }

        $this->debug = $debug;
        $this->config = $config; 

    }

    public function generateSig() { 
        $ts = $this->getDateTime();
        $hmacString = $this->config['Service'].$this->config['Operation'].$ts;
        $hmac = hash_hmac('sha1', $hmacString, $this->config['AWSKey'], true);

        $sig = base64_encode($hmac);

        return [$sig, $ts];
    }

    public function getDateTime($format = 'Y-m-d\TH:i:s\Z'){
        $date = new \DateTime('now', new DateTimeZone('UTC'));
        return $date->format($format);
    }

    public function prepareRequest(array $params, $method = 'post') { 
        $client = new Client();
        $method = strtolower($method); 
        
        $existingParams = $this->config;
        unset($existingParams['AWSKey']);

        $params = array_merge($existingParams, $params);
        ksort($params);

        return $client->$method(self::AWSMechTurkURL, $params, [
            'debug' => $this->isDebug() === true ? 'true': 'false' 
        ]);
    }

    // @TODO I should not have this type of output
    public function trySendRequest(Request $request){
        try { 
            $response = $request->send();

            return $response;
        } catch (\Guzzle\Http\Exception\CurlException  $e) {
            echo $dec->getColoredString("\n\nError:\n", 'red');
            var_dump($e);
        } catch (\Exception $e) {
            echo $dec->getColoredString("\n\nGeneral Error:\n", 'red');
            var_dump($e->getMessage());
        }
        die;
    }

    public function getConfig() { 
        return $this->config;
    }

    public function isDebug(){ 
        return $this->debug;
    }

    abstract public function checkRequestParamms(array $config);

    public static function checkRequiredKeys(array $config) { 
        return count(array_intersect_key(array_flip(static::$required_keys), $config)) === count(static::$required_keys);
    }
}
