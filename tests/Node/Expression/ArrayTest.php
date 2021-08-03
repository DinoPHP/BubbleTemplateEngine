<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ArrayExpression;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Test\NodeTestCase;

class ArrayTest extends NodeTestCase
{
    public function testConstructor()
    {
        $elements = [new ConstantExpression('foo', 1), $foo = new ConstantExpression('bar', 1)];
        $node = new ArrayExpression($elements, 1);

        $this->assertEquals($foo, $node->getNode(1));
    }

    public function getTests()
    {
        $elements = [
            new ConstantExpression('foo', 1),
            new ConstantExpression('bar', 1),

            new ConstantExpression('bar', 1),
            new ConstantExpression('foo', 1),
        ];
        $node = new ArrayExpression($elements, 1);

        return [
            [$node, '["foo" => "bar", "bar" => "foo"]'],
        ];
    }
}
