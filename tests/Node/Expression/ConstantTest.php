<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ConstantExpression;
use Bubble\Test\NodeTestCase;

class ConstantTest extends NodeTestCase
{
    public function testConstructor()
    {
        $node = new ConstantExpression('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('value'));
    }

    public function getTests()
    {
        $tests = [];

        $node = new ConstantExpression('foo', 1);
        $tests[] = [$node, '"foo"'];

        return $tests;
    }
}
