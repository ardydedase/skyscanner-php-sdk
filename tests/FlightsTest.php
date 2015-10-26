<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use Skyscanner\Transport\Flights;

date_default_timezone_set('Asia/Singapore');

/**
 * Class FlightsTest
 * @package tests
 */
class FlightsTest extends PHPUnit_Framework_TestCase
{
    const API_KEY = 'py495888586774232134437415165965';

    public function setUp()
    {
        $this->flights = new Flights(self::API_KEY);

        $dateTimeFormat = 'Y-m';
        $outboundDateTime = Date($dateTimeFormat);
        $inboundDateTime = Date($dateTimeFormat, strtotime("+31 days", $outboundDateTime));

        $this->outbound = strftime($dateTimeFormat, $outboundDateTime);
        $this->inbound = strftime($dateTimeFormat, $inboundDateTime);

        $inboundDateTime = Date($dateTimeFormat, strtotime("+3 days", $outboundDateTime));

        $dateTimeFormat = 'Y-m-d';
        $this->outboundDays = strftime(Date($dateTimeFormat));
        $this->inboundDays = strftime(Date($dateTimeFormat));

        $this->outboundDays = Date($dateTimeFormat, strtotime($this->outboundDays . ' + 30 days'));
        $this->inboundDays = Date($dateTimeFormat, strtotime($this->inboundDays . ' + 37 days'));
    }

    public function tearDown()
    {
        $this->flights = null;
    }

    public function testCreateSession()
    {
        $pollUrl = $this->flights->createSession(
            array(
                'country' => 'UK',
                'currency' => 'GBP',
                'locale' => 'en-GB',
                'originplace' => 'SIN-sky',
                'destinationplace' => 'KUL-sky',
                'outbounddate' => $this->outboundDays,
                'inbounddate' => $this->inboundDays,
                'adults' => 1
            )
        );

        $this->assertTrue(strpos($pollUrl, 'http://partners.api.skyscanner.net/apiservices/pricing') !== false);
    }

    public function testGetCheapestPriceByDate()
    {
        $flightsCacheService = new FlightsCache(self::API_KEY);
        $result = $flightsCacheService->getCheapestPriceByDate(
            array(
                'market' => 'GB',
                'currency' => 'GBP',
                'locale' => 'en-GB',
                'originplace' => 'SIN',
                'destinationplace' => 'KUL',
                'outbounddate' => $this->outboundDays,
                'inbounddate' => $this->inboundDays
            )
        )->parsed;

        $this->assertTrue(property_exists($result, 'Dates'));
        $this->assertTrue(count($result->Dates) > 0);
    }

    public function testGetCheapestPriceByRoute()
    {
        $flightsCacheService = new FlightsCache(self::API_KEY);
        $result = $flightsCacheService->getCheapestPriceByRoute(
            array(
                'market' => 'GB',
                'currency' => 'GBP',
                'locale' => 'en-GB',
                'originplace' => 'GB',
                'destinationplace' => 'DE',
                'outbounddate' => $this->outboundDays,
                'inbounddate' => $this->inboundDays
            )
        )->parsed;

        $this->assertTrue(property_exists($result, 'Routes'));
        $this->assertTrue(count($result->Routes) > 0);
    }

    public function testGetCheapestQuotes()
    {
        $flightsCacheService = new FlightsCache(self::API_KEY);
        $result = $flightsCacheService->getCheapestQuotes(
            array(
                'market' => 'GB',
                'currency' => 'GBP',
                'locale' => 'en-GB',
                'originplace' => 'SIN',
                'destinationplace' => 'KUL',
                'outbounddate' => $this->outboundDays,
                'inbounddate' => $this->inboundDays
            )
        )->parsed;

        $this->assertTrue(property_exists($result, 'Quotes'));
        $this->assertTrue(count($result->Quotes) > 0);
    }

    public function testGetResultJSON()
    {
        $flights = new Flights(self::API_KEY, 'json');
        $result = $flights->getResult(GRACEFUL, array(
            'country' => 'UK',
            'currency' => 'GBP',
            'locale' => 'en-GB',
            'originplace' => 'SIN-sky',
            'destinationplace' => 'KUL-sky',
            'outbounddate' => $this->outboundDays,
            'inbounddate' => $this->inboundDays,
            'adults' => 1
        ))->parsed;

        $this->assertTrue(property_exists($result, 'Itineraries'));
        $this->assertTrue(count($result->Itineraries) > 0);
    }
}
