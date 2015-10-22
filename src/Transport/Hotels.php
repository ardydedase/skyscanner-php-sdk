<?php

namespace Skyscanner\Transport;

/**
 * Class Hotels
 * @package Skyscanner\Transport
 * @author * *
 */
class Hotels extends Transport
{
    /**
     * @var string
     */
    private $pricingSessionUrl;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->pricingSessionUrl = self::API_HOST . '/apiservices/hotels/liveprices/v2';
        parent::__construct($apiKey);
    }

    /**
     * @param array $params
     *
     * @return string
     */
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

