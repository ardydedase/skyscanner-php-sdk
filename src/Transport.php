<?php
/**
 * @Author: ardydedase
 * @Date:   2015-09-18 00:42:32
 * @Last Modified by:   ardydedase
 * @Last Modified time: 2015-09-26 12:07:14
 */

namespace Skyscanner\Transport;

use Exception;
use BadFunctionCallException;
use UnexpectedValueException;
use BadMethodCallException;
use RuntimeException;
use Katzgrau;
use Psr\Log\LogLevel;

const STRICT = 'strict';
const GRACEFUL = 'graceful';
const IGNORE = 'ignore';

const GET = 'get';
const POST = 'post';
const PUT = 'put';

/**
 * In case HttpRequest is not available.
 * Use the native PHP Curl
 */
function httpRequest(
    $serviceUrl, 
    $headers = null, 
    $method = GET, 
    $data = null) {

    echo "\nserviceURL: $serviceUrl\n";
    echo "\nmethod: $method\n";
    var_dump($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);        
    }

    if ($method == POST) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));        
    }

    $resp = curl_exec($ch);

    curl_close($ch);    

    return $resp;
}

function getJSONStr($jsonString)
{
    // $jsonString = preg_replace("/:([]\w\.]+)/", ":\"$1\"", $jsonString);

    if (preg_match("/({.*})/", $jsonString, $matches)) {
        return $matches[0];
    }

    throw new BadJSONException(' - Syntax error, malformed JSON');
}

function getHeaders($resp)
{
    $headers = array();

    $headerStr = substr($resp, 0, strpos($resp, "\r\n\r\n"));

    foreach (explode("\r\n", $headerStr) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }

    return $headers;
}

class NotImplementedException extends BadMethodCallException {}
class ExceededRetriesException extends Exception {}
class BadJSONException extends Exception {}

class Transport 
{
    const API_HOST = 'http://partners.api.skyscanner.net';

    protected $apiKey;
    protected $marketServiceUrl;
    protected $locationAutosuggestUrl;
    protected $locationAutosuggestParams;
    protected static $responseFormat;

    public function __construct($apiKey, $responseFormat = 'json') 
    {
        self::$responseFormat = strtolower($responseFormat);

        if (!$apiKey) {
            throw new BadFunctionCallException('API Key must be specified.');
        }
        $this->apiKey = $apiKey;
        $this->marketServiceUrl = self::API_HOST . '/apiservices/reference/v1.0/countries';
        $this->locationAutosuggestUrl = self::API_HOST . '/apiservices/autosuggest/v1.0';
        $this->locationAutosuggestParams = array('market', 'currency', 'locale', 'query');
    }

    protected function makeRequest(
        $serviceUrl, 
        $method = GET,
        $headers = null, 
        $data = null, 
        $callback = null, 
        $errors = STRICT, 
        array $params = []
        ) {

        $timeout = 30;
        $errorModes = array(STRICT, GRACEFUL, IGNORE);
        $errorMode = $errors;

        if (!in_array($errorMode, $errorModes)) {
            throw new UnexpectedValueException('Possible values for errors argument are: ' . implode(", ", $errorModes));
        }

        if ($callback == null) {
            $callback = array('self', 'defaultRespCallback');            
        }

        // echo "strpos: " . strpos('apikey', strtolower($serviceUrl));
        if (strpos(strtolower($serviceUrl), 'apikey') == false) {
            echo "API key not found in: " . $serviceUrl;
            $params['apiKey'] = $this->apiKey;
        }

        if (count($params) > 0) {
            $serviceUrl .= '?' . http_build_query($params);
        }

        // if ($callback == array('self', 'getPollURL')) {
        //     unset($params['apiKey']);
        // }

        // use our own httpRequest function if HttpRequest class is not available.
        $r = httpRequest($serviceUrl, $headers, $method, $data);
        
        try {
            return call_user_func($callback, $r);
        } catch(Exception $e) {
            return self::withErrorHandling($r, null, $errorMode);    
        }
    }

    public function getMarkets($market)
    {
        $serviceUrl = "{$this->marketServiceUrl}/{$market}";
        var_dump(self::sessionHeaders());

        return $this->makeRequest($serviceUrl, GET, self::sessionHeaders());
    }

    public function locationAutosuggest(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->locationAutosuggestParams);
        $serviceUrl = "{$this->locationAutosuggestUrl}/{$paramsPath}";
        
        return $this->makeRequest($serviceUrl, $params);
    }

    public function createSession()
    {   
        throw new NotImplementedException();
    }

    public function poll(
        $pollUrl, 
        float $initialDelay = null,
        $delay = 1, 
        $tries = 10,
        $errors = STRICT,
        array $params = []
    ) {
        $initialDelay = ($initialDelay == null) ? 2.0 : $initialDelay;
        sleep($initialDelay);
        $pollResponse = null;

        for ($n = 0; $n < $tries; $n++) {
            echo "polling, tries: $n";
            $pollResponse = $this->makeRequest(
                $pollUrl, 
                GET,
                null,
                null,
                null,
                $errors,
                $params
            );

            if ($pollResponse && $this->isPollComplete($pollResponse)) {
                return $pollResponse;
            } else {
                sleep($delay);
            }
        }

        if (STRICT == $errors) {
            throw new ExceededRetriesException("Failed to poll within {$tries} tries.");
        } else {
            return $pollResponse;
        }
    }

    public function isPollComplete($pollResp)
    {
        if (!$pollResp->parsed) {
            return false;
        }

        $successList = array('UpdatesComplete', True, 'COMPLETE');
        $status = $pollResp->parsed->Status ?: $pollResp->parsed->status;
        if (!$status) {
            throw new RuntimeException('Unable to get poll response status.');
        }
        return in_array($status, $successList);
    }

    public function getResult($errors = STRICT, array $params = [])
    {
        return $this->poll($this->createSession($params), null, 1, 10, $errors);
    }    

    public static function constructParams($params, $requiredKeys, $optKeys = null) 
    {
        $params_list = array();
        foreach($requiredKeys as $requiredKey) {
            $params_list[] = $params[$requiredKey];
            if (!array_key_exists($requiredKey, $params)) {
                print("\n$requiredKey does not exist");
                throw new BadFunctionCallException("'Missing expected request parameter: $requiredKey");
            }
        }
        // TODO: optKeys
        if (is_array($optKeys)) {
            foreach($optKeys as $optKey) {
                $params_list[] = $params[$optKey];
            }
        }
        return implode($params_list, "/");
    }

    public static function sessionHeaders()
    {
        $headers = self::headers();
        $headers[] = 'content-type: application/x-www-form-urlencoded';
        return $headers;
    }

    public static function headers()
    {
        return array("accept: application/" . self::$responseFormat);
    }

    public static function defaultRespCallback($resp)
    {
        return self::parseResp($resp, self::$responseFormat);
    }

    public static function parseResp($resp, $responseFormat)
    {
        if ($responseFormat == 'json') {
            $resp = getJSONStr($resp);

            $jsonObj = json_decode($resp);

            $respObj = array();
            $respObj['parsed'] = $jsonObj;
            return (object) $respObj;
        } else if ($responseFormat == 'xml') {
            // TODO: handle XML
        }
    }

    public static function withErrorHandling($resp, $error, $mode)
    {
        echo "=================Trying with error handling...";
        // TODO
        return json_decode($resp);
    }

    public static function getPollURL($resp)
    {
        $headers = getHeaders($resp);
        return $headers['Location'];
    }

}

class Flights extends Transport
{
    private $pricingSessionUrl;

    public function __construct($apiKey)
    {
        $this->pricingSessionUrl = self::API_HOST . '/apiservices/pricing/v1.0';
        parent::__construct($apiKey);
    }

    public function createSession(array $params = [])
    {
        $callback = array('self', 'getPollURL');
        return $this->makeRequest(
            $this->pricingSessionUrl, 
            POST, 
            self::sessionHeaders(),
            $params,
            $callback
        );
    }

    public function requestBookingDetails($pollUrl, array $params = [])
    {
        $callback = array('self', 'getPollURL');
        return $this->makeRequest(
            "$pollUrl/booking",
            PUT,
            null,
            $params,
            $callback
        );
    }
}

class FlightsCache extends Flights
{
    private $reqParams = array('market', 'currency', 'locale', 'originplace', 'destinationplace', 'outbounddate');
    private $optParams = array('inbounddate'); 

    private $browseQuotesServiceUrl;
    private $browseRoutesServiceUrl;
    private $browseDatesServiceUrl;
    private $browseGridServiceUrl;

    public function __construct($apiKey)
    {   
        $this->browseQuotesServiceUrl = self::API_HOST . '/apiservices/browsequotes/v1.0';
        $this->browseRoutesServiceUrl = self::API_HOST . '/apiservices/browseroutes/v1.0';
        $this->browseDatesServiceUrl = self::API_HOST . '/apiservices/browsedates/v1.0';
        $this->browseGridServiceUrl = self::API_HOST . '/apiservices/browsegrid/v1.0';

        parent::__construct($apiKey);
    }

    public function getCheapestPriceByDate(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->reqParams, $this->optParams);
        $serviceUrl = "{$this->browseDatesServiceUrl}/{$paramsPath}";

        return $this->makeRequest($serviceUrl);        
    }

    public function getCheapestPriceByRoute(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->reqParams, $this->optParams);
        $serviceUrl = "{$this->browseRoutesServiceUrl}/{$paramsPath}";

        return $this->makeRequest($serviceUrl);        
    }   

    public function getCheapestQuotes(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->reqParams, $this->optParams);
        $serviceUrl = "{$this->browseQuotesServiceUrl}/{$paramsPath}";

        return $this->makeRequest($serviceUrl);
    }
}

class CarHire extends Transport
{
    private $pricingSessionUrl;


    public function __construct($apiKey)
    {
        $this->pricingSessionUrl = self::API_HOST . '/apiservices/carhire/liveprices/v2';

        parent::__construct($apiKey);
    }    

    public function createSession(array $params = [])
    {
        $callback = array('self', 'getPollURL');
        $reqParams = array(
            'market', 
            'currency', 
            'locale',
            'pickupplace', 
            'dropoffplace',
            'pickupdatetime',
            'dropoffdatetime',
            'driverage'            
        );
        $paramsPath = self::constructParams($params, $reqParams);
        $serviceUrl = "{$this->pricingSessionUrl}/{$paramsPath}";

        // $requestParams = array(
        //     'userip' => $params['userip']
        // );

        echo "\nSERVICE URL: $serviceUrl\n";
        $pollPath = $this->makeRequest(
            $serviceUrl,
            GET,
            null,
            null,
            $callback,
            STRICT,
            $params
        );      

        echo "\npollPath: $pollPath\n";

        return self::API_HOST . $pollPath;
    }

    public function isPollComplete($pollResp)
    {
        echo "pollResp:";
        var_dump($pollResp);

        if (!$pollResp->parsed) {
            return false;
        }

        $websites = [];

        if (self::$responseFormat == 'json') {
            $websites = $pollResp->parsed->websites;
        }

        if (count($websites) == 0) {
            return false;
        }

        foreach ($websites as $w) {
            if (!$w->in_progress) {
                echo "\nin progress: {$w->in_progress}\n";
                return false;
            }
        }
    }
}

class Hotels extends Transport
{
    private $pricingSessionUrl;

    public function __construct($apiKey)
    {
        $this->pricingSessionUrl = self::API_HOST . '/apiservices/hotels/liveprices/v2';
        parent::__construct($apiKey);
    }

    public function createSession(array $params = [])
    {
        $reqParams = array(
            'market', 
            'currency', 
            'locale',
            'entityid', 
            'checkindate', 
            'checkoutdate',
            'guests', 
            'rooms'            
        );
        $paramsPath = self::constructParams($params, $reqParams);
        $serviceUrl = "{$this->pricingSessionUrl}/{$paramsPath}";
        $callback = array('self', 'getPollURL');
        $pollPath = $this->makeRequest(
            $serviceUrl,
            GET,
            null,
            null,
            $callback,
            STRICT,
            $params
        );

        return self::API_HOST . $pollPath;
    }
}
