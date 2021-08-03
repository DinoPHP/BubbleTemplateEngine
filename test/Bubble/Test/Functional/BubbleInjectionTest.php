<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group bubble_injection
 * @group functional
 */
class Bubble_Test_Functional_BubbleInjectionTest extends PHPUnit_Framework_TestCase
{
    private $bubble;

    public function setUp()
    {
        $this->bubble = new Bubble_Engine();
    }

    /**
     * @dataProvider injectionData
     */
    public function testInjection($tpl, $data, $partials, $expect)
    {
        $this->bubble->setPartials($partials);
        $this->assertEquals($expect, $this->bubble->render($tpl, $data));
    }

    public function injectionData()
    {
        $interpolationData = array(
            'a' => '{{ b }}',
            'b' => 'FAIL',
        );

        $sectionData = array(
            'a' => true,
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        $lambdaInterpolationData = array(
            'a' => array($this, 'lambdaInterpolationCallback'),
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        $lambdaSectionData = array(
            'a' => array($this, 'lambdaSectionCallback'),
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        return array(
            array('{{ a }}',   $interpolationData, array(), '{{ b }}'),
            array('{{{ a }}}', $interpolationData, array(), '{{ b }}'),

            array('{{# a }}{{ b }}{{/ a }}',   $sectionData, array(), '{{ c }}'),
            array('{{# a }}{{{ b }}}{{/ a }}', $sectionData, array(), '{{ c }}'),

            array('{{> partial }}', $interpolationData, array('partial' => '{{ a }}'),   '{{ b }}'),
            array('{{> partial }}', $interpolationData, array('partial' => '{{{ a }}}'), '{{ b }}'),

            array('{{ a }}',           $lambdaInterpolationData, array(), '{{ c }}'),
            array('{{# a }}b{{/ a }}', $lambdaSectionData,       array(), '{{ c }}'),
        );
    }

    public static function lambdaInterpolationCallback()
    {
        return '{{ b }}';
    }

    public static function lambdaSectionCallback($text)
    {
        return '{{ ' . $text . ' }}';
    }
}
