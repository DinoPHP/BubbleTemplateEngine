<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group sections
 * @group functional
 */
class Bubble_Test_Functional_ObjectSectionTest extends PHPUnit_Framework_TestCase
{
    private $bubble;

    public function setUp()
    {
        $this->bubble = new Bubble_Engine();
    }

    public function testBasicObject()
    {
        $tpl = $this->bubble->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertEquals('Foo', $tpl->render(new Bubble_Test_Functional_Alpha()));
    }

    /**
     * @group magic_methods
     */
    public function testObjectWithGet()
    {
        $tpl = $this->bubble->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertEquals('Foo', $tpl->render(new Bubble_Test_Functional_Beta()));
    }

    /**
     * @group magic_methods
     */
    public function testSectionObjectWithGet()
    {
        $tpl = $this->bubble->loadTemplate('{{#bar}}{{#foo}}{{name}}{{/foo}}{{/bar}}');
        $this->assertEquals('Foo', $tpl->render(new Bubble_Test_Functional_Gamma()));
    }

    public function testSectionObjectWithFunction()
    {
        $tpl = $this->bubble->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $alpha = new Bubble_Test_Functional_Alpha();
        $alpha->foo = new Bubble_Test_Functional_Delta();
        $this->assertEquals('Foo', $tpl->render($alpha));
    }
}

class Bubble_Test_Functional_Alpha
{
    public $foo;

    public function __construct()
    {
        $this->foo = new StdClass();
        $this->foo->name = 'Foo';
        $this->foo->number = 1;
    }
}

class Bubble_Test_Functional_Beta
{
    protected $_data = array();

    public function __construct()
    {
        $this->_data['foo'] = new StdClass();
        $this->_data['foo']->name = 'Foo';
        $this->_data['foo']->number = 1;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }
}

class Bubble_Test_Functional_Gamma
{
    public $bar;

    public function __construct()
    {
        $this->bar = new Bubble_Test_Functional_Beta();
    }
}

class Bubble_Test_Functional_Delta
{
    protected $_name = 'Foo';

    public function name()
    {
        return $this->_name;
    }
}
