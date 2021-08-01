<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\AssignNameExpression;
use Bubble\Test\NodeTestCase;

class AssignNameTest extends NodeTestCase
{
    public function testConstructor()
    {
        $node = new AssignNameExpression('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $node = new AssignNameExpression('foo', 1);

        return [
            [$node, '$context["foo"]'],
        ];
    }
}
