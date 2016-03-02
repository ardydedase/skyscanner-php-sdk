<?php

namespace tests;

use Skyscanner\Tests\BaseSkyscannerTest;
use Skyscanner\Transport\Flights;
use Skyscanner\Transport\FlightsCache;

date_default_timezone_set('Asia/Singapore');

/**
 * Class FlightsTest
 * @package tests
 */
class FlightsTest extends BaseSkyscannerTest
{
    /**
     * @var Flights
     */
    protected $flights;
    public function setUp()
    {
        parent::setUp();
        $this->flights = new Flights($this->API_KEY);

        $dateTimeFormat = '%Y-%m';
//        $outboundDateTime = Date($dateTimeFormat);
//        $inboundDateTime = Date($dateTimeFormat, strtotime("+31 days", $outboundDateTime));

        $this->outbound = strftime($dateTimeFormat, time());
        $this->inbound = strftime($dateTimeFormat, strtotime("+31 days"));

//        $inboundDateTime = Date($dateTimeFormat, strtotime("+3 days", $outboundDateTime));

        $dateTimeFormat = '%Y-%m-%d';
        $this->outboundDays = strftime($dateTimeFormat, time()+60*60*24*30);    //Date($dateTimeFormat));
        $this->inboundDays = strftime($dateTimeFormat, time()+60*60*24*37); //Date($dateTimeFormat));

//        var_dump($this);
//        $this->outboundDays = Date($dateTimeFormat, strtotime($this->outboundDays . ' + 30 days'));
//        $this->inboundDays = Date($dateTimeFormat, strtotime($this->inboundDays . ' + 37 days'));
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
        $flightsCacheService = new FlightsCache($this->API_KEY);
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
        $flightsCacheService = new FlightsCache($this->API_KEY);
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
        $flightsCacheService = new FlightsCache($this->API_KEY);
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
        $flights = new Flights($this->API_KEY, 'json');
        $result = $flights->getResult(Flights::GRACEFUL, array(
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
