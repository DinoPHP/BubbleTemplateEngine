<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group magic_methods
 * @group functional
 */
class Bubble_Test_Functional_CallTest extends PHPUnit_Framework_TestCase
{
    public function testCallEatsContext()
    {
        $m = new Bubble_Engine();
        $tpl = $m->loadTemplate('{{# foo }}{{ label }}: {{ name }}{{/ foo }}');

        $foo = new Bubble_Test_Functional_ClassWithCall();
        $foo->name = 'Bob';

        $data = array('label' => 'name', 'foo' => $foo);

        $this->assertEquals('name: Bob', $tpl->render($data));
    }
}

class Bubble_Test_Functional_ClassWithCall
{
    public $name;

    public function __call($method, $args)
    {
        return 'unknown value';
    }
}
