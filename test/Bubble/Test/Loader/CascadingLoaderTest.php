<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Loader_CascadingLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testLoadTemplates()
    {
        $loader = new Bubble_Loader_CascadingLoader(array(
            new Bubble_Loader_ArrayLoader(array('foo' => '{{ foo }}')),
            new Bubble_Loader_ArrayLoader(array('bar' => '{{#bar}}BAR{{/bar}}')),
        ));

        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    /**
     * @expectedException Bubble_Exception_UnknownTemplateException
     */
    public function testMissingTemplatesThrowExceptions()
    {
        $loader = new Bubble_Loader_CascadingLoader(array(
            new Bubble_Loader_ArrayLoader(array('foo' => '{{ foo }}')),
            new Bubble_Loader_ArrayLoader(array('bar' => '{{#bar}}BAR{{/bar}}')),
        ));

        $loader->load('not_a_real_template');
    }
}
