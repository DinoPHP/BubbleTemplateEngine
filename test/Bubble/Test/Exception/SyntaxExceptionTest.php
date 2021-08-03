<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

class Bubble_Test_Exception_SyntaxExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $e = new Bubble_Exception_SyntaxException('whot', array('is' => 'this'));
        $this->assertTrue($e instanceof LogicException);
        $this->assertTrue($e instanceof Bubble_Exception);
    }

    public function testGetToken()
    {
        $token = array(Bubble_Tokenizer::TYPE => 'whatever');
        $e = new Bubble_Exception_SyntaxException('ignore this', $token);
        $this->assertEquals($token, $e->getToken());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new Bubble_Exception_SyntaxException('foo', array(), $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
