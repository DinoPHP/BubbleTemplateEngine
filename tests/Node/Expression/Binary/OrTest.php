<?php

namespace Bubble\Tests\Node\Expression\Binary;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\Binary\OrBinary;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Test\NodeTestCase;

class OrTest extends NodeTestCase
{
    public function testConstructor()
    {
        $left = new ConstantExpression(1, 1);
        $right = new ConstantExpression(2, 1);
        $node = new OrBinary($left, $right, 1);

        $this->assertEquals($left, $node->getNode('left'));
        $this->assertEquals($right, $node->getNode('right'));
    }

    public function getTests()
    {
        $left = new ConstantExpression(1, 1);
        $right = new ConstantExpression(2, 1);
        $node = new OrBinary($left, $right, 1);

        return [
            [$node, '(1 || 2)'],
        ];
    }
}
