<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\BlockNode;
use Bubble\Node\TextNode;
use Bubble\Test\NodeTestCase;

class BlockTest extends NodeTestCase
{
    public function testConstructor()
    {
        $body = new TextNode('foo', 1);
        $node = new BlockNode('foo', $body, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $body = new TextNode('foo', 1);
        $node = new BlockNode('foo', $body, 1);

        return [
            [$node, <<<EOF
// line 1
public function block_foo(\$context, array \$blocks = [])
{
    \$macros = \$this->macros;
    echo "foo";
}
EOF
            ],
        ];
    }
}
