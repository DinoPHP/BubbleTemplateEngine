<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

class Bubble_Test_Exception_UnknownHelperExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $e = new Bubble_Exception_UnknownHelperException('alpha');
        $this->assertTrue($e instanceof InvalidArgumentException);
        $this->assertTrue($e instanceof Bubble_Exception);
    }

    public function testMessage()
    {
        $e = new Bubble_Exception_UnknownHelperException('beta');
        $this->assertEquals('Unknown helper: beta', $e->getMessage());
    }

    public function testGetHelperName()
    {
        $e = new Bubble_Exception_UnknownHelperException('gamma');
        $this->assertEquals('gamma', $e->getHelperName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new Bubble_Exception_UnknownHelperException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
