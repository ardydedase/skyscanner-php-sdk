<?php
namespace Skyscanner\Transport;

use PHPUnit_Framework_TestCase;

date_default_timezone_set('Asia/Singapore');

const API_KEY = 'py495888586774232134437415165965';

class TransportTest extends PHPUnit_Framework_TestCase 
{
    public function setUp()
    {
        $this->transport = new Transport(API_KEY);
    }

    public function tearDown()
    {
        $this->transport = null;
    }

    public function testEmptyAPIKey() 
    {
        $this->setExpectedException('BadFunctionCallException');        
        $transport = new Transport('');
    }

    public function testGetMarketsJSON() 
    {
        $transport = new Transport(API_KEY, 'json');

        echo "testGetMarkets\n";
        $result = $transport->getMarkets('en-GB')->parsed;
        // print_r($result->Countries);

        $this->assertTrue(property_exists($result, 'Countries'));
        $this->assertTrue(count($result->Countries) > 0);
    }

    public function testMissingConstructParams()
    {   
        $this->setExpectedException('BadFunctionCallException');
        $params = array('a' => 1, 'b' => 2, 'c' => 3);
        Transport::constructParams($params, array('a', 'b', 'c', 'd'));
    }

    public function testConstructParams()
    {   
        $params = array('a' => 1, 'b' => 2, 'c' => 3);
        $this->assertEquals('1/2/3', Transport::constructParams($params, array('a', 'b', 'c')));
    }  

    public function testLocationAutosuggest()
    {
        $result = $this->transport->locationAutosuggest(array(
            'market' => 'UK',
            'currency' => 'GBP',
            'locale' => 'en-GB',
            'query' => 'kul'        
        ));

    }
}
 
class FlightsTest extends PHPUnit_Framework_TestCase 
{
    public function setUp() 
    {
        // $skyscanner = new Skyscanner();
        $this->flights = new Flights(API_KEY);

        $dateTimeFormat = 'Y-m';
        $outboundDateTime = Date($dateTimeFormat);
        echo $outboundDateTime;
        $inboundDateTime = Date($dateTimeFormat, strtotime("+31 days", $outboundDateTime));

        $this->outbound = strftime($dateTimeFormat, $outboundDateTime);
        $this->inbound = strftime($dateTimeFormat, $inboundDateTime);

        $inboundDateTime = Date($dateTimeFormat, strtotime("+3 days", $outboundDateTime));
        print "\necho: $inboundDateTime\n";
        
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
        echo "testCreateSession";
        echo $this->outboundDays;
        echo $this->inboundDays;

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
        echo "\ntestGetCheapestPriceByDate\n";
        $flightsCacheService = new FlightsCache(API_KEY);
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

        // echo "\nCOUNT: " . count($result) . "\n";
        // var_dump($result);
        $this->assertTrue(property_exists($result, 'Dates'));
        $this->assertTrue(count($result->Dates) > 0);
    }

    public function testGetCheapestPriceByRoute()
    {
        $flightsCacheService = new FlightsCache(API_KEY);
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
        $flightsCacheService = new FlightsCache(API_KEY);
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

        // echo "\ntestGetCheapestQuotes:\n";
        // var_dump($result);
        $this->assertTrue(property_exists($result, 'Quotes'));
        $this->assertTrue(count($result->Quotes) > 0);
    }

    public function testGetResultJSON()
    {
        $flights = new Flights(API_KEY, 'json');
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

        // echo "\ntestGetResultJSON:\n";
        // var_dump($result);

        $this->assertTrue(property_exists($result, 'Itineraries'));
        $this->assertTrue(count($result->Itineraries) > 0);        
    }
}

class CarHireTest extends PHPUnit_Framework_TestCase 
{
    public function setUp() 
    {
        // $skyscanner = new Skyscanner();
        $this->carHire = new CarHire(API_KEY);

        $dateTimeFormat = 'Y-m-d\TH:i';
        $pickUpDateTime = strftime(Date($dateTimeFormat));
        $dropOffDateTime = strftime(Date($dateTimeFormat)); 

        echo "pickUpDateTime:" . $pickUpDateTime . "\n";
        echo "dropOffDateTime:" . $dropOffDateTime . "\n";

        $this->pickUp = Date($dateTimeFormat, strtotime($pickUpDateTime . ' + 30 days'));
        $this->dropOff = Date($dateTimeFormat, strtotime($dropOffDateTime . ' + 37 days'));

        echo "pickUpDateTime:" . $this->pickUp . "\n";
        echo "dropOffDateTime:" . $this->dropOff . "\n";
    }

    public function tearDown()
    {
        $this->carHire = null;
    }

    public function testCreateSession()
    {
        $pollUrl = $this->carHire->createSession(
            array(
                'market' => 'UK',
                'currency' => 'GBP',
                'locale' => 'en-GB',
                'pickupplace' => 'LHR-sky',
                'dropoffplace' => 'LHR-sky',
                'pickupdatetime' => $this->pickUp,
                'dropoffdatetime' => $this->dropOff,
                'driverage' => '30',
                'userip' => '175.156.244.174'
            )
        );

        echo "pollUrl: " . $pollUrl . "\n";
    }

    public function testGetResultJSON()
    {
        $carhire = new CarHire(API_KEY);
        $result = $carhire->getResult(GRACEFUL, array(
            'market' => 'UK',
            'currency' => 'GBP',
            'locale' => 'en-GB',
            'pickupplace' => 'LHR-sky',
            'dropoffplace' => 'LHR-sky',
            'pickupdatetime' => $this->pickUp,
            'dropoffdatetime' => $this->dropOff,
            'driverage' => '30',
            'userip' => '175.156.244.174'
        ))->parsed;

        echo "\ntestGetResultJSON:\n";
        var_dump($result);

        // $this->assertTrue(property_exists($result, 'cars'));
        // $this->assertTrue(property_exists($result, 'websites'));   
    }    
}

class HotelsTest extends PHPUnit_Framework_TestCase 
{
    public function setUp()
    {
        $this->hotels = new Hotels(API_KEY);

        $dateTimeFormat = 'Y-m-d';

        $checkIn = strftime(Date($dateTimeFormat));
        $checkOut = strftime(Date($dateTimeFormat)); 

        $this->checkIn = Date($dateTimeFormat, strtotime($checkIn . ' + 30 days'));
        $this->checkOut = Date($dateTimeFormat, strtotime($checkOut . ' + 37 days'));
    }

    public function testCreateSession()
    {
        $pollUrl = $this->hotels->createSession(
            array(
                'market' => 'UK',
                'currency' => 'GBP',
                'locale' => 'en-GB',
                'entityid' => 27543923,
                'checkindate' => $this->checkIn,
                'checkoutdate' => $this->checkOut,
                'guests' => 1,
                'rooms' => 1
            )
        );

        $pollBaseUrl = 'http://partners.api.skyscanner.net/apiservices/hotels/liveprices/v2/';
        $this->assertTrue(strpos($pollUrl, $pollBaseUrl) !== false);
    }

    public function testGetResultJSON()
    {
        $result = $this->hotels->getResult(GRACEFUL, array(
            'market' => 'UK',
            'currency' => 'GBP',
            'locale' => 'en-GB',
            'entityid' => 27543923,
            'checkindate' => $this->checkIn,
            'checkoutdate' => $this->checkOut,
            'guests' => 1,
            'rooms' => 1
        ))->parsed;

        echo "\ntestGetResultJSON:\n";
        var_dump($result);

        // $this->assertTrue(property_exists($result, 'cars'));
        // $this->assertTrue(property_exists($result, 'websites'));   
    }      
}