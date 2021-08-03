<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Loader_ArrayLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $loader = new Bubble_Loader_ArrayLoader(array(
            'foo' => 'bar',
        ));

        $this->assertEquals('bar', $loader->load('foo'));
    }

    public function testSetAndLoadTemplates()
    {
        $loader = new Bubble_Loader_ArrayLoader(array(
            'foo' => 'bar',
        ));
        $this->assertEquals('bar', $loader->load('foo'));

        $loader->setTemplate('baz', 'qux');
        $this->assertEquals('qux', $loader->load('baz'));

        $loader->setTemplates(array(
            'foo' => 'FOO',
            'baz' => 'BAZ',
        ));
        $this->assertEquals('FOO', $loader->load('foo'));
        $this->assertEquals('BAZ', $loader->load('baz'));
    }

    /**
     * @expectedException Bubble_Exception_UnknownTemplateException
     */
    public function testMissingTemplatesThrowExceptions()
    {
        $loader = new Bubble_Loader_ArrayLoader();
        $loader->load('not_a_real_template');
    }
}
