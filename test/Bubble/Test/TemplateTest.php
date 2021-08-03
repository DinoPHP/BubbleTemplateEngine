<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_TemplateTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $bubble = new Bubble_Engine();
        $template = new Bubble_Test_TemplateStub($bubble);
        $this->assertSame($bubble, $template->getBubble());
    }

    public function testRendering()
    {
        $rendered = '<< wheee >>';
        $bubble = new Bubble_Engine();
        $template = new Bubble_Test_TemplateStub($bubble);
        $template->rendered = $rendered;
        $context  = new Bubble_Context();

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->assertEquals($rendered, $template());
        }

        $this->assertEquals($rendered, $template->render());
        $this->assertEquals($rendered, $template->renderInternal($context));
        $this->assertEquals($rendered, $template->render(array('foo' => 'bar')));
    }
}

class Bubble_Test_TemplateStub extends Bubble_Template
{
    public $rendered;

    public function getBubble()
    {
        return $this->bubble;
    }

    public function renderInternal(Bubble_Context $context, $indent = '', $escape = false)
    {
        return $this->rendered;
    }
}
