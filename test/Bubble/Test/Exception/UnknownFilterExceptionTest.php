<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

class Bubble_Test_Exception_UnknownFilterExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $e = new Bubble_Exception_UnknownFilterException('bacon');
        $this->assertTrue($e instanceof UnexpectedValueException);
        $this->assertTrue($e instanceof Bubble_Exception);
    }

    public function testMessage()
    {
        $e = new Bubble_Exception_UnknownFilterException('sausage');
        $this->assertEquals('Unknown filter: sausage', $e->getMessage());
    }

    public function testGetFilterName()
    {
        $e = new Bubble_Exception_UnknownFilterException('eggs');
        $this->assertEquals('eggs', $e->getFilterName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new Bubble_Exception_UnknownFilterException('foo', $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
