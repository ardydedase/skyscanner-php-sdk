<?php

namespace Skyscanner\Transport;

use PHPUnit_Framework_TestCase;

date_default_timezone_set('Asia/Singapore');

const API_KEY = 'py495888586774232134437415165965';

class CarHireTest extends PHPUnit_Framework_TestCase 
{
    public function setUp() 
    {
        $this->carHire = new CarHire(API_KEY);

        $dateTimeFormat = 'Y-m-d\TH:i';
        $pickUpDateTime = strftime(Date($dateTimeFormat));
        $dropOffDateTime = strftime(Date($dateTimeFormat));

        $this->pickUp = Date($dateTimeFormat, strtotime($pickUpDateTime . ' + 30 days'));
        $this->dropOff = Date($dateTimeFormat, strtotime($dropOffDateTime . ' + 37 days'));
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

        $this->assertTrue(property_exists($result, 'total_hotels'));
        $this->assertTrue(property_exists($result, 'total_available_hotels'));   
    }      
}