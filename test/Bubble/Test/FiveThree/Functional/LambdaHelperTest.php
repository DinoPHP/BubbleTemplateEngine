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
class Bubble_Test_FiveThree_Functional_LambdaHelperTest extends PHPUnit_Framework_TestCase
{
    private $bubble;

    public function setUp()
    {
        $this->bubble = new Bubble_Engine();
    }

    public function testSectionLambdaHelper()
    {
        $one = $this->bubble->loadTemplate('{{name}}');
        $two = $this->bubble->loadTemplate('{{#lambda}}{{name}}{{/lambda}}');

        $foo = new StdClass();
        $foo->name = 'Mario';
        $foo->lambda = function ($text, $bubble) {
            return strtoupper($bubble->render($text));
        };

        $this->assertEquals('Mario', $one->render($foo));
        $this->assertEquals('MARIO', $two->render($foo));
    }

    public function testSectionLambdaHelperRespectsDelimiterChanges()
    {
        $tpl = $this->bubble->loadTemplate("{{=<% %>=}}\n<%# bang %><% value %><%/ bang %>");

        $data = new StdClass();
        $data->value = 'hello world';
        $data->bang = function ($text, $bubble) {
            return $bubble->render($text) . '!';
        };

        $this->assertEquals('hello world!', $tpl->render($data));
    }

    public function testLambdaHelperIsInvokable()
    {
        $one = $this->bubble->loadTemplate('{{name}}');
        $two = $this->bubble->loadTemplate('{{#lambda}}{{name}}{{/lambda}}');

        $foo = new StdClass();
        $foo->name = 'Mario';
        $foo->lambda = function ($text, $render) {
            return strtoupper($render($text));
        };

        $this->assertEquals('Mario', $one->render($foo));
        $this->assertEquals('MARIO', $two->render($foo));
    }
}
