<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use Skyscanner\Tests\BaseSkyscannerTest;
use Skyscanner\Transport\Transport;

date_default_timezone_set('Asia/Singapore');

/**
 * Class TransportTest
 * Only Transport test
 * @package tests
 * @author * *
 */
class TransportTest extends BaseSkyscannerTest
{
    /**
     * @var Transport
     */
    private $transport;

    public function setUp()
    {
        parent::setUp();
        $this->transport    = new Transport($this->API_KEY);
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
        $transport = new Transport($this->API_KEY, 'json');
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

    public function ftestLocationAutosuggest()
    {
        $result = $this->transport->locationAutosuggest(array(
            'market' => 'UK',
            'currency' => 'GBP',
            'locale' => 'en-GB',
            'query' => 'kul'
        ));

    }
}
