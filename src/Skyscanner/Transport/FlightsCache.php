<?php

namespace Skyscanner\Transport;

/**
 * Class FlightsCache
 * @package Skyscanner\Transport
 * @author * *
 */
class FlightsCache extends Flights
{
    /**
     * @var array
     */
    private $reqParams = array('market', 'currency', 'locale', 'originplace', 'destinationplace', 'outbounddate');

    /**
     * @var array
     */
    private $optParams = array('inbounddate');

    /**
     * @var string
     */
    private $browseQuotesServiceUrl;

    /**
     * @var string
     */
    private $browseRoutesServiceUrl;

    /**
     * @var string
     */
    private $browseDatesServiceUrl;

    /**
     * @var string
     */
    private $browseGridServiceUrl;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->browseQuotesServiceUrl = self::API_HOST . '/apiservices/browsequotes/v1.0';
        $this->browseRoutesServiceUrl = self::API_HOST . '/apiservices/browseroutes/v1.0';
        $this->browseDatesServiceUrl = self::API_HOST . '/apiservices/browsedates/v1.0';
        $this->browseGridServiceUrl = self::API_HOST . '/apiservices/browsegrid/v1.0';

        parent::__construct($apiKey);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getCheapestPriceByDate(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->reqParams, $this->optParams);
        $serviceUrl = "{$this->browseDatesServiceUrl}/{$paramsPath}";

        return $this->makeRequest($serviceUrl);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getCheapestPriceByRoute(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->reqParams, $this->optParams);
        $serviceUrl = "{$this->browseRoutesServiceUrl}/{$paramsPath}";

        return $this->makeRequest($serviceUrl);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getCheapestQuotes(array $params = [])
    {
        $paramsPath = self::constructParams($params, $this->reqParams, $this->optParams);
        $serviceUrl = "{$this->browseQuotesServiceUrl}/{$paramsPath}";

        return $this->makeRequest($serviceUrl);
    }
}