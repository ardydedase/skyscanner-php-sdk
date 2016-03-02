<?php
/**
 * Project: skyscanner-php-sdk
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */

namespace Skyscanner\Tests;

use PHPUnit_Framework_TestCase;

class BaseSkyscannerTest extends PHPUnit_Framework_TestCase
{
    protected $API_KEY    =   'see phpunit.xml';

    public function setUp()
    {
        $this->API_KEY      =   $_ENV['SKYSCANNER_APIKEY'];
    }
}