<?php
/**
 * Project: skyscanner-php-sdk
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */

namespace Skyscanner\Tests;

use PHPUnit_Framework_TestCase;

abstract class BaseSkyscannerTest extends PHPUnit_Framework_TestCase
{
    protected $API_KEY    =   'see phpunit.xml';

    public function setUp()
    {
        // _TRAVISCI is checked first, for use with Travis-CI
        $this->API_KEY      =   getenv('SKYSCANNER_APIKEY_TRAVISCI');

        if(!empty($_ENV['SKYSCANNER_APIKEY']))
            $this->API_KEY      =   $_ENV['SKYSCANNER_APIKEY'];
    }
}