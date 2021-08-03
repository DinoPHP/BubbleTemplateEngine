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
class Bubble_Test_FiveThree_Functional_StrictCallablesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider callables
     */
    public function testStrictCallables($strict, $name, $section, $expected)
    {
        $bubble = new Bubble_Engine(array('strict_callables' => $strict));
        $tpl      = $bubble->loadTemplate('{{# section }}{{ name }}{{/ section }}');

        $data = new StdClass();
        $data->name    = $name;
        $data->section = $section;

        $this->assertEquals($expected, $tpl->render($data));
    }

    public function callables()
    {
        $lambda = function ($tpl, $bubble) {
            return strtoupper($bubble->render($tpl));
        };

        return array(
            // Interpolation lambdas
            array(
                false,
                array($this, 'instanceName'),
                $lambda,
                'YOSHI',
            ),
            array(
                false,
                array(__CLASS__, 'staticName'),
                $lambda,
                'YOSHI',
            ),
            array(
                false,
                function () {
                    return 'Yoshi';
                },
                $lambda,
                'YOSHI',
            ),

            // Section lambdas
            array(
                false,
                'Yoshi',
                array($this, 'instanceCallable'),
                'YOSHI',
            ),
            array(
                false,
                'Yoshi',
                array(__CLASS__, 'staticCallable'),
                'YOSHI',
            ),
            array(
                false,
                'Yoshi',
                $lambda,
                'YOSHI',
            ),

            // Strict interpolation lambdas
            array(
                true,
                function () {
                    return 'Yoshi';
                },
                $lambda,
                'YOSHI',
            ),

            // Strict section lambdas
            array(
                true,
                'Yoshi',
                array($this, 'instanceCallable'),
                'YoshiYoshi',
            ),
            array(
                true,
                'Yoshi',
                array(__CLASS__, 'staticCallable'),
                'YoshiYoshi',
            ),
            array(
                true,
                'Yoshi',
                function ($tpl, $bubble) {
                    return strtoupper($bubble->render($tpl));
                },
                'YOSHI',
            ),
        );
    }

    public function instanceCallable($tpl, $bubble)
    {
        return strtoupper($bubble->render($tpl));
    }

    public static function staticCallable($tpl, $bubble)
    {
        return strtoupper($bubble->render($tpl));
    }

    public function instanceName()
    {
        return 'Yoshi';
    }

    public static function staticName()
    {
        return 'Yoshi';
    }
}
