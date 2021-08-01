<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\BlockReferenceNode;
use Bubble\Test\NodeTestCase;

class BlockReferenceTest extends NodeTestCase
{
    public function testConstructor()
    {
        $node = new BlockReferenceNode('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        return [
            [new BlockReferenceNode('foo', 1), <<<EOF
// line 1
\$this->displayBlock('foo', \$context, \$blocks);
EOF
            ],
        ];
    }
}
