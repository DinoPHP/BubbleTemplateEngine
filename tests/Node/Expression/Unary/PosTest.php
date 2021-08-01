<?php

namespace Bubble\Tests\Node\Expression\Unary;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\Unary\PosUnary;
use Bubble\Test\NodeTestCase;

class PosTest extends NodeTestCase
{
    public function testConstructor()
    {
        $expr = new ConstantExpression(1, 1);
        $node = new PosUnary($expr, 1);

        $this->assertEquals($expr, $node->getNode('node'));
    }

    public function getTests()
    {
        $node = new ConstantExpression(1, 1);
        $node = new PosUnary($node, 1);

        return [
            [$node, '+1'],
        ];
    }
}
