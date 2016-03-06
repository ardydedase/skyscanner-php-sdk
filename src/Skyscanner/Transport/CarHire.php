<?php

namespace Skyscanner\Transport;

/**
 * Class CarHire
 * @package Skyscanner\Transport
 * @author * *
 */
class CarHire extends Transport
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
        $this->pricingSessionUrl = self::API_HOST . '/apiservices/carhire/liveprices/v2';

        parent::__construct($apiKey);
    }

    /**
     * @param array $params
     *
     * @return string
     */
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

    /**
     * @param $pollResp
     *
     * @return bool
     */
    public function isPollComplete($pollResp)
    {
        if (!$pollResp->parsed) {
            return false;
        }

        $websites = [];

        if ($this->getResponseFormat() === 'json') {
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

