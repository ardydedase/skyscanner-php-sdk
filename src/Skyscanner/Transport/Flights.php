<?php

namespace Skyscanner\Transport;

/**
 * Class Flights
 * @package Skyscanner\Transport
 * @author * *
 */
class Flights extends Transport
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
        $this->pricingSessionUrl = self::API_HOST . '/apiservices/pricing/v1.0';
        parent::__construct($apiKey);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function createSession(array $params = [])
    {
        $callback = array('self', 'getPollURL');
        return $this->makeRequest(
            $this->pricingSessionUrl,
            self::POST,
            $this->sessionHeaders(),
            $params,
            $callback
        );
    }

    /**
     * @param $pollUrl
     * @param array $params
     *
     * @return mixed
     */
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
