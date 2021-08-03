<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group lambdas
 * @group functional
 */
class Bubble_Test_FiveThree_Functional_HigherOrderSectionsTest extends PHPUnit_Framework_TestCase
{
    private $bubble;

    public function setUp()
    {
        $this->bubble = new Bubble_Engine();
    }

    public function testAnonymousFunctionSectionCallback()
    {
        $tpl = $this->bubble->loadTemplate('{{#wrapper}}{{name}}{{/wrapper}}');

        $foo = new Bubble_Test_FiveThree_Functional_Foo();
        $foo->name = 'Mario';
        $foo->wrapper = function ($text) {
            return sprintf('<div class="anonymous">%s</div>', $text);
        };

        $this->assertEquals(sprintf('<div class="anonymous">%s</div>', $foo->name), $tpl->render($foo));
    }

    public function testSectionCallback()
    {
        $one = $this->bubble->loadTemplate('{{name}}');
        $two = $this->bubble->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $foo = new Bubble_Test_FiveThree_Functional_Foo();
        $foo->name = 'Luigi';

        $this->assertEquals($foo->name, $one->render($foo));
        $this->assertEquals(sprintf('<em>%s</em>', $foo->name), $two->render($foo));
    }

    public function testViewArrayAnonymousSectionCallback()
    {
        $tpl = $this->bubble->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $data = array(
            'name' => 'Bob',
            'wrap' => function ($text) {
                return sprintf('[[%s]]', $text);
            },
        );

        $this->assertEquals(sprintf('[[%s]]', $data['name']), $tpl->render($data));
    }
}

class Bubble_Test_FiveThree_Functional_Foo
{
    public $name  = 'Justin';
    public $lorem = 'Lorem ipsum dolor sit amet,';
    public $wrap;

    public function __construct()
    {
        $this->wrap = function ($text) {
            return sprintf('<em>%s</em>', $text);
        };
    }
}
