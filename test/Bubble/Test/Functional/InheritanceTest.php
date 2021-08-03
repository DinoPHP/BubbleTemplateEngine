<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group inheritance
 * @group functional
 */
class Bubble_Test_Functional_InheritanceTest extends PHPUnit_Framework_TestCase
{
    private $bubble;

    public function setUp()
    {
        $this->bubble = new Bubble_Engine(array(
            'pragmas' => array(Bubble_Engine::PRAGMA_BLOCKS),
        ));
    }

    public function getIllegalInheritanceExamples()
    {
        return array(
            array(
                array(
                    'foo' => '{{$baz}}default content{{/baz}}',
                ),
                array(
                    'bar' => 'set by user',
                ),
                '{{< foo }}{{# bar }}{{$ baz }}{{/ baz }}{{/ bar }}{{/ foo }}',
            ),
            array(
                array(
                    'foo' => '{{$baz}}default content{{/baz}}',
                ),
                array(
                ),
                '{{<foo}}{{^bar}}{{$baz}}set by template{{/baz}}{{/bar}}{{/foo}}',
            ),
            array(
                array(
                    'foo' => '{{$baz}}default content{{/baz}}',
                    'qux' => 'I am a partial',
                ),
                array(
                ),
                '{{<foo}}{{>qux}}{{$baz}}set by template{{/baz}}{{/foo}}',
            ),
            array(
                array(
                    'foo' => '{{$baz}}default content{{/baz}}',
                ),
                array(),
                '{{<foo}}{{=<% %>=}}<%={{ }}=%>{{/foo}}',
            ),
        );
    }

    public function getLegalInheritanceExamples()
    {
        return array(
            array(
                array(
                    'foo' => '{{$baz}}default content{{/baz}}',
                ),
                array(
                    'bar' => 'set by user',
                ),
                '{{<foo}}{{bar}}{{$baz}}override{{/baz}}{{/foo}}',
                'override',
            ),
            array(
                array(
                    'foo' => '{{$baz}}default content{{/baz}}',
                ),
                array(
                ),
                '{{<foo}}{{! ignore me }}{{$baz}}set by template{{/baz}}{{/foo}}',
                'set by template',
            ),
            array(
                array(
                    'foo' => '{{$baz}}defualt content{{/baz}}',
                ),
                array(),
                '{{<foo}}set by template{{$baz}}also set by template{{/baz}}{{/foo}}',
                'also set by template',
            ),
            array(
                array(
                    'foo' => '{{$a}}FAIL!{{/a}}',
                    'bar' => 'WIN!!',
                ),
                array(),
                '{{<foo}}{{$a}}{{<bar}}FAIL{{/bar}}{{/a}}{{/foo}}',
                'WIN!!',
            ),
        );
    }

    public function testDefaultContent()
    {
        $tpl = $this->bubble->loadTemplate('{{$title}}Default title{{/title}}');

        $data = array();

        $this->assertEquals('Default title', $tpl->render($data));
    }

    public function testDefaultContentRendersVariables()
    {
        $tpl = $this->bubble->loadTemplate('{{$foo}}default {{bar}} content{{/foo}}');

        $data = array(
            'bar' => 'baz',
        );

        $this->assertEquals('default baz content', $tpl->render($data));
    }

    public function testDefaultContentRendersTripleBubbleVariables()
    {
        $tpl = $this->bubble->loadTemplate('{{$foo}}default {{{bar}}} content{{/foo}}');

        $data = array(
            'bar' => '<baz>',
        );

        $this->assertEquals('default <baz> content', $tpl->render($data));
    }

    public function testDefaultContentRendersSections()
    {
        $tpl = $this->bubble->loadTemplate(
            '{{$foo}}default {{#bar}}{{baz}}{{/bar}} content{{/foo}}'
        );

        $data = array(
            'bar' => array('baz' => 'qux'),
        );

        $this->assertEquals('default qux content', $tpl->render($data));
    }

    public function testDefaultContentRendersNegativeSections()
    {
        $tpl = $this->bubble->loadTemplate(
            '{{$foo}}default {{^bar}}{{baz}}{{/bar}} content{{/foo}}'
        );

        $data = array(
            'foo' => array('bar' => 'qux'),
            'baz' => 'three',
        );

        $this->assertEquals('default three content', $tpl->render($data));
    }

    public function testBubbleInjectionInDefaultContent()
    {
        $tpl = $this->bubble->loadTemplate(
            '{{$foo}}default {{#bar}}{{baz}}{{/bar}} content{{/foo}}'
        );

        $data = array(
            'bar' => array('baz' => '{{qux}}'),
        );

        $this->assertEquals('default {{qux}} content', $tpl->render($data));
    }

    public function testDefaultContentRenderedInsideIncludedTemplates()
    {
        $partials = array(
            'include' => '{{$foo}}default content{{/foo}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<include}}{{/include}}'
        );

        $data = array();

        $this->assertEquals('default content', $tpl->render($data));
    }

    public function testOverriddenContent()
    {
        $partials = array(
            'super' => '...{{$title}}Default title{{/title}}...',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<super}}{{$title}}sub template title{{/title}}{{/super}}'
        );

        $data = array();

        $this->assertEquals('...sub template title...', $tpl->render($data));
    }

    public function testOverriddenPartial()
    {
        $partials = array(
            'partial' => '|{{$stuff}}...{{/stuff}}{{$default}} default{{/default}}|',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            'test {{<partial}}{{$stuff}}override1{{/stuff}}{{/partial}} {{<partial}}{{$stuff}}override2{{/stuff}}{{/partial}}'
        );

        $data = array();

        $this->assertEquals('test |override1 default| |override2 default|', $tpl->render($data));
    }

    public function testBlocksDoNotLeakBetweenPartials()
    {
        $partials = array(
            'partial' => '|{{$a}}A{{/a}} {{$b}}B{{/b}}|',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            'test {{<partial}}{{$a}}C{{/a}}{{/partial}} {{<partial}}{{$b}}D{{/b}}{{/partial}}'
        );

        $data = array();

        $this->assertEquals('test |C B| |A D|', $tpl->render($data));
    }

    public function testDataDoesNotOverrideBlock()
    {
        $partials = array(
            'include' => '{{$var}}var in include{{/var}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<include}}{{$var}}var in template{{/var}}{{/include}}'
        );

        $data = array(
            'var' => 'var in data',
        );

        $this->assertEquals('var in template', $tpl->render($data));
    }

    public function testDataDoesNotOverrideDefaultBlockValue()
    {
        $partials = array(
            'include' => '{{$var}}var in include{{/var}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<include}}{{/include}}'
        );

        $data = array(
            'var' => 'var in data',
        );

        $this->assertEquals('var in include', $tpl->render($data));
    }

    public function testOverridePartialWithNewlines()
    {
        $partials = array(
            'partial' => '{{$ballmer}}peaking{{/ballmer}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            "{{<partial}}{{\$ballmer}}\npeaked\n\n:(\n{{/ballmer}}{{/partial}}"
        );

        $data = array();

        $this->assertEquals("peaked\n\n:(\n", $tpl->render($data));
    }

    public function testInheritIndentationWhenOverridingAPartial()
    {
        $partials = array(
            'partial' => 'stop:
                    {{$nineties}}collaborate and listen{{/nineties}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<partial}}{{$nineties}}hammer time{{/nineties}}{{/partial}}'
        );

        $data = array();

        $this->assertEquals(
            'stop:
                    hammer time',
            $tpl->render($data)
        );
    }

    public function testInheritSpacingWhenOverridingAPartial()
    {
        $partials = array(
            'parent' => 'collaborate_and{{$id}}{{/id}}',
            'child'  => '{{<parent}}{{$id}}_listen{{/id}}{{/parent}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            'stop:
              {{>child}}'
        );

        $data = array();

        $this->assertEquals(
            'stop:
              collaborate_and_listen',
            $tpl->render($data)
        );
    }

    public function testOverrideOneSubstitutionButNotTheOther()
    {
        $partials = array(
            'partial' => '{{$stuff}}default one{{/stuff}}, {{$stuff2}}default two{{/stuff2}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<partial}}{{$stuff2}}override two{{/stuff2}}{{/partial}}'
        );

        $data = array();

        $this->assertEquals('default one, override two', $tpl->render($data));
    }

    public function testSuperTemplatesWithNoParameters()
    {
        $partials = array(
            'include' => '{{$foo}}default content{{/foo}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{>include}}|{{<include}}{{/include}}'
        );

        $data = array();

        $this->assertEquals('default content|default content', $tpl->render($data));
    }

    public function testRecursionInInheritedTemplates()
    {
        $partials = array(
            'include'  => '{{$foo}}default content{{/foo}} {{$bar}}{{<include2}}{{/include2}}{{/bar}}',
            'include2' => '{{$foo}}include2 default content{{/foo}} {{<include}}{{$bar}}don\'t recurse{{/bar}}{{/include}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<include}}{{$foo}}override{{/foo}}{{/include}}'
        );

        $data = array();

        $this->assertEquals('override override override don\'t recurse', $tpl->render($data));
    }

    public function testTopLevelSubstitutionsTakePrecedenceInMultilevelInheritance()
    {
        $partials = array(
            'parent'      => '{{<older}}{{$a}}p{{/a}}{{/older}}',
            'older'       => '{{<grandParent}}{{$a}}o{{/a}}{{/grandParent}}',
            'grandParent' => '{{$a}}g{{/a}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<parent}}{{$a}}c{{/a}}{{/parent}}'
        );

        $data = array();

        $this->assertEquals('c', $tpl->render($data));
    }

    public function testMultiLevelInheritanceNoSubChild()
    {
        $partials = array(
            'parent'      => '{{<older}}{{$a}}p{{/a}}{{/older}}',
            'older'       => '{{<grandParent}}{{$a}}o{{/a}}{{/grandParent}}',
            'grandParent' => '{{$a}}g{{/a}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<parent}}{{/parent}}'
        );

        $data = array();

        $this->assertEquals('p', $tpl->render($data));
    }

    public function testIgnoreTextInsideSuperTemplatesButParseArgs()
    {
        $partials = array(
            'include' => '{{$foo}}default content{{/foo}}',
         );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<include}} asdfasd {{$foo}}hmm{{/foo}} asdfasdfasdf {{/include}}'
        );

        $data = array();

        $this->assertEquals('hmm', $tpl->render($data));
    }

    public function testIgnoreTextInsideSuperTemplates()
    {
        $partials = array(
            'include' => '{{$foo}}default content{{/foo}}',
         );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<include}} asdfasd asdfasdfasdf {{/include}}'
        );

        $data = array();

        $this->assertEquals('default content', $tpl->render($data));
    }

    public function testInheritanceWithLazyEvaluation()
    {
        $partials = array(
            'parent' => '{{#items}}{{$value}}ignored{{/value}}{{/items}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<parent}}{{$value}}<{{ . }}>{{/value}}{{/parent}}'
        );

        $data = array('items' => array(1, 2, 3));

        $this->assertEquals('<1><2><3>', $tpl->render($data));
    }

    public function testInheritanceWithLazyEvaluationWhitespaceIgnored()
    {
        $partials = array(
            'parent' => '{{#items}}{{$value}}\n\nignored\n\n{{/value}}{{/items}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<parent}}\n\n\n{{$value}}<{{ . }}>{{/value}}\n\n{{/parent}}'
        );

        $data = array('items' => array(1, 2, 3));

        $this->assertEquals('<1><2><3>', $tpl->render($data));
    }

    public function testInheritanceWithLazyEvaluationAndSections()
    {
        $partials = array(
            'parent' => '{{#items}}{{$value}}\n\nignored {{.}} {{#more}} there is more {{/more}}\n\n{{/value}}{{/items}}',
        );

        $this->bubble->setPartials($partials);

        $tpl = $this->bubble->loadTemplate(
            '{{<parent}}\n\n\n{{$value}}<{{ . }}>{{#more}} there is less {{/more}}{{/value}}\n\n{{/parent}}'
        );

        $data = array('items' => array(1, 2, 3), 'more' => 'stuff');

        $this->assertEquals('<1> there is less <2> there is less <3> there is less ', $tpl->render($data));
    }

    /**
     * @dataProvider getIllegalInheritanceExamples
     * @expectedException Bubble_Exception_SyntaxException
     * @expectedExceptionMessage Illegal content in < parent tag
     */
    public function testIllegalInheritanceExamples($partials, $data, $template)
    {
        $this->bubble->setPartials($partials);
        $tpl = $this->bubble->loadTemplate($template);
        $tpl->render($data);
    }

    /**
     * @dataProvider getLegalInheritanceExamples
     */
    public function testLegalInheritanceExamples($partials, $data, $template, $expect)
    {
        $this->bubble->setPartials($partials);
        $tpl = $this->bubble->loadTemplate($template);
        $this->assertSame($expect, $tpl->render($data));
    }
}
