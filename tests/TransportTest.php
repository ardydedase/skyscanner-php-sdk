<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use Skyscanner\Transport\Transport;

date_default_timezone_set('Asia/Singapore');

/**
 * Class TransportTest
 * Only Transport test
 * @package tests
 * @author * *
 */
class TransportTest extends PHPUnit_Framework_TestCase
{
    const API_KEY = 'py495888586774232134437415165965';

    private $transport;

    public function setUp()
    {
        $this->transport = new Transport(self::API_KEY);
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
        $transport = new Transport(self::API_KEY, 'json');
        $result = $transport->getMarkets('en-GB')->parsed;
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
