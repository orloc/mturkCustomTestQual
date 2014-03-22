<?php

namespace MechWrapper\Request;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Exception\CurlException;
use MechWrapper\Utility\Colors;

abstract class AbstractAWSRequest { 

    const AWSLiveUrl = 'https://mechanicalturk.amazonnaws.com';
    const AWSDebugUrl = 'https://mechanicalturk.sandbox.amazonaws.com';

    protected $activeUrl;

    protected $config;

    protected $debug;

    protected $request = null;

    protected static $required_keys = [ 
        'AWSAccessKeyId',
        'AWSKey', 
        'Service', 
        'Operation'
    ];

    public function __construct(array $config, array $required, $debug = false) { 
        $keys = array_merge($required, self::$required_keys);

        if (!self::checkRequiredKeys($keys, $config)) { 
            throw new \Exception(sprintf("Config must contain all of the following keys: %s", implode($keys, ', ')));
        }

        $this->debug = $debug;
        $this->config = $config; 

        $this->activeUrl = $this->isDebug() 
            ? self::AWSDebugUrl
            : self::AWSLiveUrl;
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

    public function prepareRequest($method = 'post') { 

        $client = new Client();
        $method = strtolower($method); 
        
        $params = $this->config;
        unset($params['AWSKey']);
        ksort($params);

        $this->request = $client->$method($this->getUrl(), $params, [
            'debug' => $this->isDebug() === true ? 'true': 'false' 
        ]);

        return $this->request;
    }

    // @TODO I should not have this type of output
    public function trySendRequest(){
        $request = $this->request;
        $dec = new Colors();
        try { 
            $response = $request->send();

            return $response;
        } catch (\Guzzle\Http\Exception\CurlException  $e) {
            var_dump($e->getMessage());
            echo $dec->getColoredString("\n\nError:\n", 'red');
        } catch (\Exception $e) {
            echo $dec->getColoredString("\n\nGeneral Error:\n", 'red');
        }
        die;
    }

    public function addConfigParam($key, $val) { 
        if (!isset($this->config[$key])){ 
            $this->config[$key] = $val;
            return $this->getConfig();
        }
        return false;
    }

    public function addXmlParam($xml, $key) { 
        if (file_exists($xml)) { 
            $xml = simplexml_load_file($xml);
            $this->addConfigParam($key, (string)$xml->asXml());

            return $this->getConfig();
        }

        return false;
    }

    public function getConfig() { 
        return $this->config;
    }

    public function getUrl(){ 
        return $this->activeUrl;
    }

    public function isDebug(){ 
        return $this->debug;
    }


    public static function checkRequiredKeys(array $required, array $config) { 
        return count(array_intersect_key(array_flip($required), $config)) === count($required);
    }
}
