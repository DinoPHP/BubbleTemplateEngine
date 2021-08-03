<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\DoNode;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Test\NodeTestCase;

class DoTest extends NodeTestCase
{
    public function testConstructor()
    {
        $expr = new ConstantExpression('foo', 1);
        $node = new DoNode($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = [];

        $expr = new ConstantExpression('foo', 1);
        $node = new DoNode($expr, 1);
        $tests[] = [$node, "// line 1\n\"foo\";"];

        return $tests;
    }
}
