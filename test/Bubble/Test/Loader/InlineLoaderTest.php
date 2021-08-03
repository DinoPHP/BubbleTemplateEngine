<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Loader_InlineLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testLoadTemplates()
    {
        $loader = new Bubble_Loader_InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    /**
     * @expectedException Bubble_Exception_UnknownTemplateException
     */
    public function testMissingTemplatesThrowExceptions()
    {
        $loader = new Bubble_Loader_InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $loader->load('not_a_real_template');
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testInvalidOffsetThrowsException()
    {
        new Bubble_Loader_InlineLoader(__FILE__, 'notanumber');
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testInvalidFileThrowsException()
    {
        new Bubble_Loader_InlineLoader('notarealfile', __COMPILER_HALT_OFFSET__);
    }
}

__halt_compiler();

@@ foo
{{ foo }}

@@ bar
{{#bar}}BAR{{/bar}}
