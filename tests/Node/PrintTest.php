<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\PrintNode;
use Bubble\Test\NodeTestCase;

class PrintTest extends NodeTestCase
{
    public function testConstructor()
    {
        $expr = new ConstantExpression('foo', 1);
        $node = new PrintNode($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = [];
        $tests[] = [new PrintNode(new ConstantExpression('foo', 1), 1), "// line 1\necho \"foo\";"];

        return $tests;
    }
}
