<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

class Bubble_Test_Exception_UnknownTemplateExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $e = new Bubble_Exception_UnknownTemplateException('mario');
        $this->assertTrue($e instanceof InvalidArgumentException);
        $this->assertTrue($e instanceof Bubble_Exception);
    }

    public function testMessage()
    {
        $e = new Bubble_Exception_UnknownTemplateException('luigi');
        $this->assertEquals('Unknown template: luigi', $e->getMessage());
    }

    public function testGetTemplateName()
    {
        $e = new Bubble_Exception_UnknownTemplateException('yoshi');
        $this->assertEquals('yoshi', $e->getTemplateName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new Bubble_Exception_UnknownTemplateException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
