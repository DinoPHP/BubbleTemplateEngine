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
class Bubble_Test_FiveThree_Functional_ClosureQuirksTest extends PHPUnit_Framework_TestCase
{
    private $bubble;

    public function setUp()
    {
        $this->bubble = new Bubble_Engine();
    }

    public function testClosuresDontLikeItWhenYouTouchTheirProperties()
    {
        $tpl = $this->bubble->loadTemplate('{{ foo.bar }}');
        $this->assertEquals('', $tpl->render(array('foo' => function () {
            return 'FOO';
        })));
    }
}
