<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ArrayExpression;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\GetAttrExpression;
use Bubble\Node\Expression\NameExpression;
use Bubble\Template;
use Bubble\Test\NodeTestCase;

class GetAttrTest extends NodeTestCase
{
    public function testConstructor()
    {
        $expr = new NameExpression('foo', 1);
        $attr = new ConstantExpression('bar', 1);
        $args = new ArrayExpression([], 1);
        $args->addElement(new NameExpression('foo', 1));
        $args->addElement(new ConstantExpression('bar', 1));
        $node = new GetAttrExpression($expr, $attr, $args, Template::ARRAY_CALL, 1);

        $this->assertEquals($expr, $node->getNode('node'));
        $this->assertEquals($attr, $node->getNode('attribute'));
        $this->assertEquals($args, $node->getNode('arguments'));
        $this->assertEquals(Template::ARRAY_CALL, $node->getAttribute('type'));
    }

    public function getTests()
    {
        $tests = [];

        $expr = new NameExpression('foo', 1);
        $attr = new ConstantExpression('bar', 1);
        $args = new ArrayExpression([], 1);
        $node = new GetAttrExpression($expr, $attr, $args, Template::ANY_CALL, 1);
        $tests[] = [$node, sprintf('%s%s, "bar", [], "any", false, false, false, 1)', $this->getAttributeGetter(), $this->getVariableGetter('foo', 1))];

        $node = new GetAttrExpression($expr, $attr, $args, Template::ARRAY_CALL, 1);
        $tests[] = [$node, '(($__internal_%s = // line 1'."\n".
            '($context["foo"] ?? null)) && is_array($__internal_%s) || $__internal_%s instanceof ArrayAccess ? ($__internal_%s["bar"] ?? null) : null)', null, true, ];

        $args = new ArrayExpression([], 1);
        $args->addElement(new NameExpression('foo', 1));
        $args->addElement(new ConstantExpression('bar', 1));
        $node = new GetAttrExpression($expr, $attr, $args, Template::METHOD_CALL, 1);
        $tests[] = [$node, sprintf('%s%s, "bar", [0 => %s, 1 => "bar"], "method", false, false, false, 1)', $this->getAttributeGetter(), $this->getVariableGetter('foo', 1), $this->getVariableGetter('foo'))];

        return $tests;
    }
}
