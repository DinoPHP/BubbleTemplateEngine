<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\NameExpression;
use Bubble\Node\Expression\NullCoalesceExpression;
use Bubble\Test\NodeTestCase;

class NullCoalesceTest extends NodeTestCase
{
    public function getTests()
    {
        $left = new NameExpression('foo', 1);
        $right = new ConstantExpression(2, 1);
        $node = new NullCoalesceExpression($left, $right, 1);

        return [[$node, "((// line 1\n\$context[\"foo\"]) ?? (2))"]];
    }
}
